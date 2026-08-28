<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Central service for backup, restore, and zip creation.
 *
 * Backup files are stored on the `local` disk under: /app/backups/
 *   - lynera-backup-YYYYmmdd-HHiiss.json          (data only)
 *   - lynera-backup-YYYYmmdd-HHiiss.zip           (data + uploaded files)
 */
class BackupService
{
    /** Tables that must never be dumped or restored. */
    public const SKIP_TABLES = [
        'migrations', 'cache', 'cache_locks', 'jobs', 'job_batches',
        'failed_jobs', 'sessions', 'password_reset_tokens',
        // Audit trail must survive restore for forensic integrity.
        'backend_audit_logs',
    ];

    /**
     * Return all backup-eligible table names (ordered).
     *
     * @return array<int, string>
     */
    public function backupTables(): array
    {
        return collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->reject(fn (string $t) => in_array($t, self::SKIP_TABLES, true))
            ->values()
            ->all();
    }

    /**
     * Build a full JSON payload of all tenant data.
     *
     * @return array<string, mixed>
     */
    public function buildJsonPayload(?string $actorEmail = null): array
    {
        $tables = $this->backupTables();

        return [
            'app' => config('app.name', 'LYNERA'),
            'database' => config('database.connections.'.config('database.default').'.database'),
            'generated_at' => now()->toIso8601String(),
            'generated_by' => $actorEmail,
            'schema_version' => 1,
            'tables' => collect($tables)->mapWithKeys(fn (string $t) => [
                $t => DB::table($t)->get()->map(fn ($row) => (array) $row)->all(),
            ])->all(),
        ];
    }

    /**
     * Write the JSON payload to a local file inside storage/app/backups/.
     * Returns the absolute path of the written file.
     */
    public function writeJsonFile(array $payload, ?string $filename = null): string
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir, 0755, true);

        $filename ??= 'lynera-backup-'.now()->format('Ymd-His').'.json';
        $path = $dir.DIRECTORY_SEPARATOR.$filename;

        File::put(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $path;
    }

    /**
     * Create a .zip file that contains the JSON payload + every uploaded file
     * inside storage/app/public/. Returns absolute path to the zip.
     */
    public function writeZipFile(array $payload, ?string $filename = null): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip belum aktif. Install php-zip lalu ulangi.');
        }

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir, 0755, true);

        $filename ??= 'lynera-backup-'.now()->format('Ymd-His').'.zip';
        $path = $dir.DIRECTORY_SEPARATOR.$filename;

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak bisa membuat file zip: '.$path);
        }

        // 1. JSON payload
        $zip->addFromString(
            'data.json',
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        // 2. Uploaded files (public disk => storage/app/public)
        $publicRoot = storage_path('app/public');
        if (File::isDirectory($publicRoot)) {
            foreach (File::allFiles($publicRoot) as $file) {
                $rel = ltrim(str_replace($publicRoot, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                $zip->addFile($file->getPathname(), 'storage/'.$rel);
            }
        }

        // 3. README
        $zip->addFromString('README.txt', $this->zipReadme($payload));

        $zip->close();

        return $path;
    }

    /**
     * List every existing backup file inside storage/app/backups/ (newest first).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listBackups(): array
    {
        $dir = storage_path('app/backups');
        if (! File::isDirectory($dir)) {
            return [];
        }

        return collect(File::files($dir))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['json', 'zip'], true))
            ->map(fn ($f) => [
                'name' => $f->getFilename(),
                'ext' => strtolower($f->getExtension()),
                'size' => $f->getSize(),
                'size_human' => $this->humanBytes($f->getSize()),
                'modified' => Carbon::createFromTimestamp($f->getMTime())->toIso8601String(),
                'modified_human' => Carbon::createFromTimestamp($f->getMTime())->diffForHumans(),
                'path' => $f->getPathname(),
            ])
            ->sortByDesc('modified')
            ->values()
            ->all();
    }

    public function pathFor(string $filename): ?string
    {
        // Prevent path traversal.
        if (str_contains($filename, '/') || str_contains($filename, '\\') || str_contains($filename, '..')) {
            return null;
        }
        $path = storage_path('app/backups'.DIRECTORY_SEPARATOR.$filename);

        return File::exists($path) ? $path : null;
    }

    public function delete(string $filename): bool
    {
        $path = $this->pathFor($filename);

        return $path && File::delete($path);
    }

    /**
     * Estimate the total number of rows across all backup tables.
     */
    public function estimateRows(): int
    {
        $total = 0;
        foreach ($this->backupTables() as $t) {
            $total += DB::table($t)->count();
        }

        return $total;
    }

    /**
     * Restore data from an uploaded JSON file.
     *
     * Behaviour:
     *   - Truncates every dumped table (respecting FK order via disable checks).
     *   - Re-inserts the backup rows.
     *   - Preserves the currently-logged-in super_admin user (its row is re-inserted
     *     from the current DB after the wipe, so admin never logs itself out).
     *
     * @return array{tables:int, rows:int, skipped:array<int,string>}
     */
    public function restoreFromJsonFile(string $absolutePath, ?int $preserveUserId = null): array
    {
        if (! File::exists($absolutePath)) {
            throw new RuntimeException('File backup tidak ditemukan.');
        }

        $raw = File::get($absolutePath);
        $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($payload) || ! isset($payload['tables']) || ! is_array($payload['tables'])) {
            throw new RuntimeException('Format backup tidak valid (missing "tables").');
        }

        $tables = $payload['tables'];
        $skipped = [];
        $insertedRows = 0;
        $insertedTables = 0;

        // Snapshot current super admin (so they can still log in after restore).
        $preservedUser = null;
        if ($preserveUserId !== null) {
            $preservedUser = DB::table('users')->where('id', $preserveUserId)->first();
        }

        DB::transaction(function () use ($tables, &$insertedRows, &$insertedTables, &$skipped, $preservedUser) {
            // NOTE: FOREIGN_KEY_CHECKS is session-scoped and can be inside the transaction.
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            try {
                foreach ($tables as $table => $rows) {
                if (in_array($table, self::SKIP_TABLES, true)) {
                    $skipped[] = $table;
                    continue;
                }
                if (! Schema::hasTable($table)) {
                    $skipped[] = $table.' (tidak ada di skema saat ini)';
                    continue;
                }
                if (! is_array($rows)) {
                    continue;
                }

                // Use DELETE (rollback-safe) instead of TRUNCATE, which triggers
                // an implicit commit on MySQL/MariaDB and would break the tx.
                DB::table($table)->delete();

                if (! empty($rows)) {
                    // Insert in chunks to stay within packet size.
                    foreach (array_chunk($rows, 200) as $chunk) {
                        // Cast every column to a safe scalar / json string.
                        $chunk = array_map(function (array $row) {
                            foreach ($row as $k => $v) {
                                if (is_array($v) || is_object($v)) {
                                    $row[$k] = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                }
                            }
                            return $row;
                        }, $chunk);

                        DB::table($table)->insert($chunk);
                        $insertedRows += count($chunk);
                    }
                }

                $insertedTables++;
            }

            // Re-inject the super admin row so the current session survives.
            if ($preservedUser !== null && Schema::hasTable('users')) {
                DB::table('users')->updateOrInsert(
                    ['id' => $preservedUser->id],
                    (array) $preservedUser
                );
            }
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        });

        return [
            'tables' => $insertedTables,
            'rows' => $insertedRows,
            'skipped' => $skipped,
        ];
    }

    /**
     * Restore from an uploaded file (JSON or ZIP containing data.json).
     */
    public function restoreFromUpload(UploadedFile $file, ?int $preserveUserId = null): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'json') {
            $tmp = $file->getRealPath();
            return $this->restoreFromJsonFile($tmp, $preserveUserId);
        }

        if ($ext === 'zip') {
            if (! class_exists(ZipArchive::class)) {
                throw new RuntimeException('Ekstensi PHP zip belum aktif.');
            }

            $tmpDir = storage_path('app/backups/_restore_'.uniqid('', true));
            File::ensureDirectoryExists($tmpDir, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($file->getRealPath()) !== true) {
                throw new RuntimeException('File .zip tidak bisa dibuka.');
            }
            $zip->extractTo($tmpDir);
            $zip->close();

            $jsonPath = $tmpDir.DIRECTORY_SEPARATOR.'data.json';
            if (! File::exists($jsonPath)) {
                File::deleteDirectory($tmpDir);
                throw new RuntimeException('Isi zip tidak berisi data.json.');
            }

            $result = $this->restoreFromJsonFile($jsonPath, $preserveUserId);

            // Restore storage files if present.
            $storageDir = $tmpDir.DIRECTORY_SEPARATOR.'storage';
            if (File::isDirectory($storageDir)) {
                $publicRoot = storage_path('app/public');
                File::ensureDirectoryExists($publicRoot, 0755, true);
                File::copyDirectory($storageDir, $publicRoot);
                $result['files_restored'] = true;
            }

            File::deleteDirectory($tmpDir);

            return $result;
        }

        throw new RuntimeException('Format file tidak didukung. Gunakan .json atau .zip.');
    }

    /**
     * Delete backups older than the retention window (in days).
     */
    public function pruneOlderThan(int $days): int
    {
        $dir = storage_path('app/backups');
        if (! File::isDirectory($dir)) {
            return 0;
        }

        $cutoff = now()->subDays($days)->timestamp;
        $count = 0;

        foreach (File::files($dir) as $file) {
            if (! in_array(strtolower($file->getExtension()), ['json', 'zip'], true)) {
                continue;
            }
            if ($file->getMTime() < $cutoff) {
                @unlink($file->getPathname());
                $count++;
            }
        }

        return $count;
    }

    /** Emails responsible for receiving scheduled backup notifications. */
    public function superAdminEmails(): array
    {
        return User::query()
            ->where('role', 'super_admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->all();
    }

    public function humanBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes.' B';
        $units = ['KB','MB','GB','TB'];
        $i = -1;
        do { $bytes /= 1024; $i++; } while ($bytes >= 1024 && $i < count($units) - 1);
        return round($bytes, 2).' '.$units[$i];
    }

    private function zipReadme(array $payload): string
    {
        return <<<TXT
LYNERA Backup Package
=====================
Application  : {$payload['app']}
Database     : {$payload['database']}
Generated At : {$payload['generated_at']}
Generated By : {$payload['generated_by']}
Schema Ver.  : {$payload['schema_version']}

Contents
--------
- data.json     : JSON dump of every backup-eligible table
- storage/      : All files stored under storage/app/public/
                  (payment proofs, logos, QR uploads, dst.)

Cara Restore
------------
1. Login sebagai super admin.
2. Buka menu Backend > Backup.
3. Klik "Pulihkan Backup" lalu pilih file .zip ini.
4. Konfirmasi. Data lama akan ditimpa oleh isi backup.

PERHATIAN: Restore bersifat destruktif. Simpan backup terbaru
sebelum menjalankan restore di lingkungan produksi.
TXT;
    }
}
