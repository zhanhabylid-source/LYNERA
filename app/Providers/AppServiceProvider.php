<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Support/subscription_helpers.php');

        // Force HTTPS scheme when running behind Emergent preview proxy
        if (config('app.env') !== 'testing') {
            URL::forceScheme('https');
        }

        VerifyEmail::toMailUsing(function (object $notifiable, string $verificationUrl): MailMessage {
            return (new MailMessage)
                ->subject('Verifikasi Akun LYNERA')
                ->view('emails.notification', [
                    'preheader' => 'Verifikasi email untuk mengaktifkan akun LYNERA Anda.',
                    'badge' => 'Keamanan Akun',
                    'heading' => 'Verifikasi Email Anda',
                    'intro' => sprintf('Halo %s, selamat datang di LYNERA. Konfirmasikan alamat email ini agar akun dan seluruh fitur bisnis Anda aktif dengan aman.', $notifiable->name),
                    'details' => [
                        'Email' => (string) $notifiable->email,
                        'Masa Berlaku' => (int) config('auth.verification.expire', 60).' menit',
                    ],
                    'actionLabel' => 'Verifikasi Email',
                    'actionUrl' => $verificationUrl,
                    'outro' => 'Jika Anda tidak membuat akun LYNERA, abaikan email ini.',
                    'securityNote' => 'Demi keamanan, jangan teruskan tautan verifikasi ini kepada siapa pun.',
                ]);
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
            $expireMinutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new MailMessage)
                ->subject('Reset Password LYNERA')
                ->view('emails.notification', [
                    'preheader' => 'Gunakan tautan aman untuk membuat password LYNERA baru.',
                    'badge' => 'Keamanan Akun',
                    'heading' => 'Atur Ulang Password',
                    'intro' => sprintf('Halo %s, kami menerima permintaan untuk mengatur ulang password akun LYNERA Anda.', $notifiable->name),
                    'details' => [
                        'Email' => (string) $notifiable->email,
                        'Masa Berlaku' => $expireMinutes.' menit',
                    ],
                    'actionLabel' => 'Buat Password Baru',
                    'actionUrl' => $resetUrl,
                    'outro' => 'Jika Anda tidak meminta reset password, abaikan email ini dan akun Anda tetap aman.',
                    'securityNote' => 'Jangan bagikan tautan reset password kepada siapa pun.',
                ]);
        });
    }
}
