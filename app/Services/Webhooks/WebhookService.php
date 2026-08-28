<?php

namespace App\Services\Webhooks;

class WebhookService
{
    public function dispatchEvent(string $event, array $payload): void
    {
        // Future scaling:
        // - sign payload
        // - enqueue webhook delivery jobs
        // - retry with exponential backoff
    }
}
