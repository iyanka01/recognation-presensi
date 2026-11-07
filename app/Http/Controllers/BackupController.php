<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ifsnop\Mysqldump as IMysqldump;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Exception;

class BackupController extends Controller
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function index()
    {
        $files = File::files($this->backupPath);
        $backups = [];
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => round($file->getSize() / 1024, 2) . ' KB',
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime()
                ];
            }
        }
        
        // Sort by newest first
        usort($backups, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return view('backup.index', compact('backups'));
    }

    public function create()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $userName = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            
            $dsn = "mysql:host={$host};dbname={$dbName}";
            
            $filename = 'backup_' . date('Y_m_d_His') . '.sql';
            $filePath = $this->backupPath . '/' . $filename;
            
            $dump = new IMysqldump\Mysqldump($dsn, $userName, $password);
            $dump->start($filePath);

            return redirect()->route('backup.index')->with('success', 'Backup database berhasil dibuat!');
        } catch (Exception $e) {
            return redirect()->route('backup.index')->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function download($file)
    {
        // Cegah Path Traversal (Arbitrary File Download)
        $cleanFilename = basename($file);
        if (!str_ends_with($cleanFilename, '.sql')) {
            return redirect()->route('backup.index')->with('error', 'Format file backup tidak valid.');
        }

        $filePath = $this->backupPath . DIRECTORY_SEPARATOR . $cleanFilename;
        $realPath = realpath($filePath);
        $realBackupDir = realpath($this->backupPath);

        if (!$realPath || !str_starts_with($realPath, $realBackupDir) || !File::exists($realPath)) {
            return redirect()->route('backup.index')->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download($realPath);
    }

    public function destroy($file)
    {
        // Cegah Path Traversal (Arbitrary File Deletion)
        $cleanFilename = basename($file);
        if (!str_ends_with($cleanFilename, '.sql')) {
            return redirect()->route('backup.index')->with('error', 'Format file backup tidak valid.');
        }

        $filePath = $this->backupPath . DIRECTORY_SEPARATOR . $cleanFilename;
        $realPath = realpath($filePath);
        $realBackupDir = realpath($this->backupPath);

        if (!$realPath || !str_starts_with($realPath, $realBackupDir) || !File::exists($realPath)) {
            return redirect()->route('backup.index')->with('error', 'File backup tidak ditemukan.');
        }

        File::delete($realPath);
        return redirect()->route('backup.index')->with('success', 'File backup berhasil dihapus.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200' // Maksimal 50MB untuk mencegah DoS memory exhaustion
        ]);

        try {
            $file = $request->file('backup_file');
            
            // Verifikasi ekstensi file secara ketat
            if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
                return redirect()->route('backup.index')->with('error', 'File harus berformat .sql');
            }

            $sql = file_get_contents($file->getRealPath());
            
            // Execute raw SQL
            DB::unprepared($sql);

            return redirect()->route('backup.index')->with('success', 'Database berhasil dipulihkan (Restore)!');
        } catch (Exception $e) {
            return redirect()->route('backup.index')->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }
}
