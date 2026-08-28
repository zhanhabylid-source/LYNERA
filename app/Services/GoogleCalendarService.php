<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CalendarIntegration;
use App\Models\GoogleCalendarToken;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleCalendarService
{
    public function createEvent(Booking $booking): ?string
    {
        return $this->safeSync(function () use ($booking) {
            $calendar = $this->calendarClientForTenant((int) $booking->tenant_id);
            if ($calendar === null) {
                return null;
            }

            $event = $calendar->events->insert($this->calendarId(), $this->buildEvent($booking));

            return $event->getId();
        }, 'google_calendar_create_event', ['booking_id' => $booking->id]);
    }

    public function updateEvent(Booking $booking): ?string
    {
        return $this->safeSync(function () use ($booking) {
            $calendar = $this->calendarClientForTenant((int) $booking->tenant_id);
            if ($calendar === null) {
                return null;
            }

            $eventId = $booking->google_event_id;
            if ($eventId === null || $eventId === '') {
                $createdId = $this->createEvent($booking);

                return $createdId;
            }

            $calendar->events->update($this->calendarId(), $eventId, $this->buildEvent($booking));

            return $eventId;
        }, 'google_calendar_update_event', ['booking_id' => $booking->id]);
    }

    public function deleteEvent(string $eventId, ?int $tenantId = null): void
    {
        if ($eventId === '') {
            return;
        }

        $this->safeSync(function () use ($eventId, $tenantId) {
            $resolvedTenantId = $tenantId ?? (int) auth()->id();
            $calendar = $this->calendarClientForTenant($resolvedTenantId);
            if ($calendar === null) {
                return null;
            }

            $calendar->events->delete($this->calendarId(), $eventId);

            return null;
        }, 'google_calendar_delete_event', [
            'event_id' => $eventId,
            'tenant_id' => $tenantId,
        ]);
    }

    public function syncBooking(Booking $booking): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        try {
            if ($booking->status === Booking::STATUS_CANCELED) {
                $this->detachBooking($booking);

                return;
            }

            if ($booking->status !== Booking::STATUS_CONFIRMED) {
                return;
            }

            $eventId = $booking->google_event_id
                ? $this->updateEvent($booking)
                : $this->createEvent($booking);

            if ($eventId !== null && $eventId !== '') {
                $this->persistEventId($booking, $eventId);
            }
        } catch (Throwable $exception) {
            Log::error('Google Calendar sync failed.', [
                'booking_id' => $booking->id,
                'tenant_id' => $booking->tenant_id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function detachBooking(Booking $booking): void
    {
        try {
            if (! empty($booking->google_event_id) && $this->isConfigured()) {
                $this->deleteEvent((string) $booking->google_event_id, (int) $booking->tenant_id);
            }
        } catch (Throwable $exception) {
            Log::warning('Google Calendar detach failed.', [
                'booking_id' => $booking->id,
                'tenant_id' => $booking->tenant_id,
                'message' => $exception->getMessage(),
            ]);
        }

        $booking->forceFill(['google_event_id' => null])->saveQuietly();
        CalendarIntegration::query()->where('booking_id', $booking->id)->delete();
    }

    public function storeTokenForUser(int $userId, array $tokenPayload): void
    {
        $expiresAt = isset($tokenPayload['created'], $tokenPayload['expires_in'])
            ? Carbon::createFromTimestamp((int) $tokenPayload['created'])->addSeconds((int) $tokenPayload['expires_in'])
            : (isset($tokenPayload['expires_in']) ? now()->addSeconds((int) $tokenPayload['expires_in']) : null);

        GoogleCalendarToken::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'access_token' => (string) ($tokenPayload['access_token'] ?? ''),
                'refresh_token' => $tokenPayload['refresh_token'] ?? null,
                'expires_at' => $expiresAt,
                'token_type' => $tokenPayload['token_type'] ?? null,
                'scope' => $tokenPayload['scope'] ?? null,
            ]
        );
    }

    public function getAuthUrl(string $state): ?string
    {
        $client = $this->baseClient();
        if ($client === null) {
            return null;
        }

        $client->setState($state);

        return $client->createAuthUrl();
    }

    public function fetchAccessTokenWithCode(string $code): ?array
    {
        $client = $this->baseClient();
        if ($client === null) {
            return null;
        }

        $token = $client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            Log::warning('Google OAuth callback returned an error.', ['token' => $token]);

            return null;
        }

        return $token;
    }

    private function calendarClientForTenant(int $tenantId): ?Calendar
    {
        $client = $this->baseClient();
        if ($client === null) {
            return null;
        }

        $token = GoogleCalendarToken::query()->where('user_id', $tenantId)->first();
        if ($token === null) {
            return null;
        }

        $client->setAccessToken([
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'expires_in' => max(0, now()->diffInSeconds($token->expires_at ?? now(), false)),
            'created' => $token->expires_at?->subSeconds(max(0, now()->diffInSeconds($token->expires_at ?? now(), false)))->timestamp ?? now()->timestamp,
            'token_type' => $token->token_type,
            'scope' => $token->scope,
        ]);

        if ($client->isAccessTokenExpired()) {
            if (empty($token->refresh_token)) {
                return null;
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken($token->refresh_token);
            if (isset($newToken['error'])) {
                Log::warning('Google access token refresh failed.', ['tenant_id' => $tenantId, 'token' => $newToken]);

                return null;
            }

            $this->storeTokenForUser($tenantId, array_merge($newToken, [
                'refresh_token' => $newToken['refresh_token'] ?? $token->refresh_token,
            ]));

            $freshToken = GoogleCalendarToken::query()->where('user_id', $tenantId)->first();
            if ($freshToken === null) {
                return null;
            }

            $client->setAccessToken([
                'access_token' => $freshToken->access_token,
                'refresh_token' => $freshToken->refresh_token,
                'token_type' => $freshToken->token_type,
                'scope' => $freshToken->scope,
            ]);
        }

        return new Calendar($client);
    }

    private function buildEvent(Booking $booking): Event
    {
        $booking->loadMissing(['service', 'customer']);

        $start = Carbon::parse($booking->booking_date?->format('Y-m-d').' '.$booking->booking_time);
        $end = Carbon::parse($booking->booking_date?->format('Y-m-d').' '.$booking->end_time);
        $timezone = config('app.timezone', 'Asia/Singapore');

        $event = new Event();
        $event->setSummary(sprintf('%s - %s', $booking->service?->name ?? 'Service', $booking->customer?->name ?? 'Customer'));
        $event->setDescription(trim(implode("\n", array_filter([
            'Customer: '.($booking->customer?->name ?? '-'),
            'Phone: '.($booking->customer?->phone ?? '-'),
            'Location: '.($booking->location ?? '-'),
            'Notes: '.($booking->notes ?? '-'),
        ]))));
        $event->setLocation($booking->location);
        $event->setStart(new EventDateTime([
            'dateTime' => $start->toIso8601String(),
            'timeZone' => $timezone,
        ]));
        $event->setEnd(new EventDateTime([
            'dateTime' => $end->toIso8601String(),
            'timeZone' => $timezone,
        ]));

        return $event;
    }

    private function persistEventId(Booking $booking, string $eventId): void
    {
        $booking->forceFill(['google_event_id' => $eventId])->saveQuietly();

        CalendarIntegration::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            ['google_event_id' => $eventId]
        );
    }

    private function baseClient(): ?GoogleClient
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $client = new GoogleClient();
        $client->setClientId((string) config('services.google_calendar.client_id'));
        $client->setClientSecret((string) config('services.google_calendar.client_secret'));
        $client->setRedirectUri((string) config('services.google_calendar.redirect_uri'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            Calendar::CALENDAR,
        ]);

        return $client;
    }

    private function isConfigured(): bool
    {
        return (string) config('services.google_calendar.client_id') !== ''
            && (string) config('services.google_calendar.client_secret') !== ''
            && (string) config('services.google_calendar.redirect_uri') !== '';
    }

    private function calendarId(): string
    {
        return (string) config('services.google_calendar.calendar_id', 'primary');
    }

    private function safeSync(callable $callback, string $logContext, array $context = []): mixed
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            Log::warning($logContext, array_merge($context, [
                'message' => $exception->getMessage(),
            ]));

            return null;
        }
    }
}
