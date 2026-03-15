<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--keep=7 : Number of backups to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a daily database backup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting database backup...');
        
        $startTime = microtime(true);
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $backupFileName = "attendance_backup_{$timestamp}.sql";
        
        try {
            // Get database configuration
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port', 3306);
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            
            // Determine backup path
            $backupPath = storage_path('app/backups');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }
            
            $fullPath = $backupPath . '/' . $backupFileName;
            
            // Build mysqldump command
            $command = sprintf(
                'mysqldump -h%s -P%s -u%s -p%s %s > "%s" 2>&1',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($fullPath)
            );
            
            // Execute backup
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                $this->error('Database backup failed!');
                Log::error('Database backup failed', ['output' => $output]);
                return Command::FAILURE;
            }
            
            // Verify backup file was created
            if (!File::exists($fullPath)) {
                $this->error('Backup file was not created');
                return Command::FAILURE;
            }
            
            $fileSize = File::size($fullPath);
            $this->info("Backup created successfully: {$backupFileName} (" . number_format($fileSize / 1024, 2) . " KB)");
            
            // Clean old backups
            $keep = (int) $this->option('keep');
            $this->cleanupOldBackups($backupPath, $keep);
            
            $duration = round(microtime(true) - $startTime, 2);
            $this->info("Backup completed in {$duration} seconds");
            
            // Log success
            Log::info('Database backup completed', [
                'file' => $backupFileName,
                'size' => $fileSize,
                'duration' => $duration
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            Log::error('Database backup error', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }
    
    /**
     * Clean up old backup files
     */
    private function cleanupOldBackups(string $path, int $keep): void
    {
        $files = File::files($path);
        
        // Sort by modification time (newest first)
        usort($files, function ($a, $b) {
            return File::lastModified($b) - File::lastModified($a);
        });
        
        // Delete old backups
        $deleted = 0;
        foreach (array_slice($files, $keep) as $file) {
            if (File::delete($file->getPathname())) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old backup(s)");
        }
    }
}