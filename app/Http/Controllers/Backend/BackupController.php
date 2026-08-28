<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BackendAuditLogService;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(
        private readonly BackendAuditLogService $auditLogService,
        private readonly BackupService $backupService,
    ) {
    }

    public function index(): View
    {
        return view('backend.backup.index', [
            'tenantCount' => User::query()->where('role', '!=', 'super_admin')->count(),
            'tables' => collect($this->backupService->backupTables()),
            'backups' => $this->backupService->listBackups(),
            'totalRows' => $this->backupService->estimateRows(),
        ]);
    }

    /** Download JSON dump (streaming, not persisted). */
    public function download(Request $request): StreamedResponse
    {
        $filename = 'lynera-backup-'.now()->format('Ymd-His').'.json';
        $payload = $this->backupService->buildJsonPayload(optional($request->user())->email);

        $this->auditLogService->log(
            action: 'tenant_data_backup_downloaded',
            targetType: 'backup',
            targetId: 0,
            targetLabel: $filename,
            meta: ['format' => 'json'],
            request: $request,
        );

        return response()->streamJson($payload, 200, [
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Download .zip that also contains storage/app/public files. */
    public function downloadZip(Request $request): BinaryFileResponse
    {
        $payload = $this->backupService->buildJsonPayload(optional($request->user())->email);
        $path = $this->backupService->writeZipFile($payload);
        $filename = basename($path);

        $this->auditLogService->log(
            action: 'tenant_data_backup_downloaded',
            targetType: 'backup',
            targetId: 0,
            targetLabel: $filename,
            meta: ['format' => 'zip', 'size' => filesize($path)],
            request: $request,
        );

        return response()->download($path, $filename)->deleteFileAfterSend(false);
    }

    /** Download a previously generated (scheduled) backup file. */
    public function downloadStored(string $filename, Request $request): BinaryFileResponse
    {
        $path = $this->backupService->pathFor($filename);
        abort_if($path === null, 404);

        $this->auditLogService->log(
            action: 'scheduled_backup_downloaded',
            targetType: 'backup',
            targetId: 0,
            targetLabel: $filename,
            meta: ['size' => filesize($path)],
            request: $request,
        );

        return response()->download($path, $filename);
    }

    public function destroyStored(string $filename, Request $request): RedirectResponse
    {
        $ok = $this->backupService->delete($filename);

        $this->auditLogService->log(
            action: $ok ? 'backup_file_deleted' : 'backup_file_delete_failed',
            targetType: 'backup',
            targetId: 0,
            targetLabel: $filename,
            meta: [],
            request: $request,
        );

        return redirect()->route('backend.backup.index')
            ->with($ok ? 'success' : 'error', $ok ? 'Backup dihapus.' : 'Gagal menghapus backup.');
    }

    public function restore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'backup_file' => 'required|file|mimes:json,zip|max:102400', // max 100 MB
            'confirm' => 'required|in:PULIHKAN',
        ], [
            'backup_file.mimes' => 'File harus berformat .json atau .zip.',
            'confirm.in' => 'Ketik PULIHKAN (huruf besar) untuk konfirmasi.',
        ]);

        try {
            $result = $this->backupService->restoreFromUpload(
                $data['backup_file'],
                (int) $request->user()->id,
            );
        } catch (\Throwable $e) {
            $this->auditLogService->log(
                action: 'backup_restore_failed',
                targetType: 'backup',
                targetId: 0,
                targetLabel: $data['backup_file']->getClientOriginalName(),
                meta: ['error' => $e->getMessage()],
                request: $request,
            );

            return redirect()->route('backend.backup.index')
                ->with('error', 'Gagal memulihkan: '.$e->getMessage());
        }

        $this->auditLogService->log(
            action: 'backup_restored',
            targetType: 'backup',
            targetId: 0,
            targetLabel: $data['backup_file']->getClientOriginalName(),
            meta: $result,
            request: $request,
        );

        return redirect()->route('backend.backup.index')->with(
            'success',
            "Berhasil memulihkan {$result['rows']} baris dari {$result['tables']} tabel."
            .(!empty($result['skipped']) ? ' Dilewati: '.implode(', ', $result['skipped']).'.' : '')
        );
    }
}
