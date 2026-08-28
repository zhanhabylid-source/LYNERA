<?php

namespace App\Console\Commands;

use App\Services\BackendAuditLogService;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RunAutoBackup extends Command
{
    protected $signature = 'lynera:auto-backup
                            {--zip : Sertakan file unggahan dalam .zip}
                            {--keep=14 : Simpan berapa hari backup lama}
                            {--notify : Kirim email pemberitahuan ke super admin}';

    protected $description = 'Buat backup data tenant otomatis dan simpan di storage/app/backups/.';

    public function handle(BackupService $backupService, BackendAuditLogService $auditLogService): int
    {
        $this->info('Mulai backup otomatis...');

        $payload = $backupService->buildJsonPayload('system@scheduler');

        if ($this->option('zip')) {
            $path = $backupService->writeZipFile($payload);
        } else {
            $path = $backupService->writeJsonFile($payload);
        }

        $sizeHuman = $backupService->humanBytes((int) filesize($path));
        $this->info('Backup tersimpan: '.$path.' ('.$sizeHuman.')');

        // Prune old files
        $keep = (int) $this->option('keep');
        if ($keep > 0) {
            $deleted = $backupService->pruneOlderThan($keep);
            if ($deleted > 0) {
                $this->info("Menghapus {$deleted} backup lama (> {$keep} hari).");
            }
        }

        // Audit log (actor_id null since system)
        try {
            $auditLogService->log(
                action: 'scheduled_backup_created',
                targetType: 'backup',
                targetId: 0,
                targetLabel: basename($path),
                meta: ['size' => filesize($path), 'zip' => (bool) $this->option('zip')],
            );
        } catch (\Throwable $e) {
            // Non-fatal
            Log::warning('audit log failed for scheduled backup: '.$e->getMessage());
        }

        // Optional email notification
        if ($this->option('notify')) {
            $emails = $backupService->superAdminEmails();
            if (! empty($emails)) {
                try {
                    Mail::raw(
                        "Backup terjadwal berhasil dibuat.\n\nFile   : ".basename($path)."\nUkuran : {$sizeHuman}\nWaktu  : ".now()->toDateTimeString()."\n\nAkses melalui halaman Backend > Backup.",
                        function ($msg) use ($emails, $path) {
                            $msg->to($emails)
                                ->subject('[LYNERA] Backup terjadwal: '.basename($path));
                        }
                    );
                    $this->info('Notifikasi dikirim ke: '.implode(', ', $emails));
                } catch (\Throwable $e) {
                    $this->warn('Gagal mengirim email: '.$e->getMessage());
                    Log::warning('scheduled backup email failed: '.$e->getMessage());
                }
            }
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
