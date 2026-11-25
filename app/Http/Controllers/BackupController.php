<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Backup\BackupService;
use App\Http\Requests\Backup\TarikApiRequest;
use Exception;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        $backups = $this->backupService->listBackups();
        $departemens = \App\Models\Departemen::orderBy('nama_departemen')->get();
        return view('backup.index', compact('backups', 'departemens'));
    }

    public function create()
    {
        try {
            $filename = $this->backupService->createBackup();
            \App\Services\Security\SecurityLogService::log('backup.create', 'admin', auth()->id(), auth()->user()?->username, ['filename' => $filename]);
            return redirect()->route('backup.index')
                ->with('success', 'Backup database berhasil dibuat!');
        } catch (Exception $e) {
            return redirect()->route('backup.index')
                ->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function download($file)
    {
        try {
            $path = $this->backupService->resolvePath($file);
            \App\Services\Security\SecurityLogService::log('backup.download', 'admin', auth()->id(), auth()->user()?->username, ['filename' => $file]);
            return response()->download($path);
        } catch (Exception $e) {
            return redirect()->route('backup.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy($file)
    {
        try {
            $this->backupService->deleteBackup($file);
            \App\Services\Security\SecurityLogService::log('backup.delete', 'admin', auth()->id(), auth()->user()?->username, ['filename' => $file]);
            return redirect()->route('backup.index')
                ->with('success', 'File backup berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->route('backup.index')
                ->with('error', $e->getMessage());
        }
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200',
        ]);

        try {
            $clientFileName = $request->file('backup_file')->getClientOriginalName();
            $this->backupService->restoreBackup($request->file('backup_file'));
            \App\Services\Security\SecurityLogService::log('backup.restore', 'admin', auth()->id(), auth()->user()?->username, ['filename' => $clientFileName]);
            return redirect()->route('backup.index')
                ->with('success', 'Database berhasil dipulihkan (Restore)!');
        } catch (Exception $e) {
            return redirect()->route('backup.index')
                ->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }

    public function tarikApi(TarikApiRequest $request)
    {
        try {
            $result = $this->backupService->tarikApi($request->all());
            \App\Services\Security\SecurityLogService::log('migration.pull', 'admin', auth()->id(), auth()->user()?->username, ['api_url' => $request->input('api_url')]);
            return redirect()->route('backup.index')
                ->with('success', $result['message']);
        } catch (Exception $e) {
            return redirect()->route('backup.index')
                ->with('error', $e->getMessage());
        }
    }
}
?>
