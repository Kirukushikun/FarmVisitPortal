<?php

namespace App\Console\Commands;

use App\Models\Permit;
use App\Models\PermitPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanupPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned permit photos from database and storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No files will be deleted');
        }

        // Step 1: Find orphaned database records (photos without existing permits)
        $this->info("\n=== Step 1: Checking orphaned database records ===");
        
        $validPermitIds = Permit::pluck('id')->toArray();
        $orphanedPhotoRecords = PermitPhoto::whereNotIn('permit_id', $validPermitIds)->get();
        
        $this->info("Found {$orphanedPhotoRecords->count()} orphaned photo records in database");
        
        if ($orphanedPhotoRecords->count() > 0) {
            if ($dryRun) {
                $this->table(['ID', 'Permit ID', 'File Path', 'Created At'], 
                    $orphanedPhotoRecords->map(fn($photo) => [
                        $photo->id,
                        $photo->permit_id,
                        $photo->file_path,
                        $photo->created_at
                    ])
                );
            } else {
                $deleted = PermitPhoto::whereNotIn('permit_id', $validPermitIds)->delete();
                $this->info("Deleted {$deleted} orphaned photo records from database");
            }
        }

        // Step 2: Find files in storage that don't exist in database
        $this->info("\n=== Step 2: Checking orphaned files in storage ===");
        
        $dbFilePaths = PermitPhoto::pluck('file_path')->filter()->toArray();
        
        // Get all files in the photos directory
        $storagePath = public_path('photos');
        $orphanedFiles = [];
        
        if (is_dir($storagePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storagePath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $relativePath = str_replace(public_path() . '/', '', $file->getPathname());
                    
                    if (!in_array($relativePath, $dbFilePaths)) {
                        $orphanedFiles[] = [
                            'path' => $relativePath,
                            'size' => $this->formatBytes($file->getSize()),
                            'modified' => date('Y-m-d H:i:s', $file->getMTime())
                        ];
                    }
                }
            }
        }
        
        $this->info("Found " . count($orphanedFiles) . " orphaned files in storage");
        
        if (count($orphanedFiles) > 0) {
            if ($dryRun) {
                $this->table(['File Path', 'Size', 'Last Modified'], $orphanedFiles);
            } else {
                $deletedCount = 0;
                $totalSize = 0;
                
                foreach ($orphanedFiles as $file) {
                    $fullPath = public_path($file['path']);
                    if (file_exists($fullPath)) {
                        $totalSize += filesize($fullPath);
                        if (unlink($fullPath)) {
                            $deletedCount++;
                        } else {
                            $this->warn("Failed to delete: {$file['path']}");
                        }
                    }
                }
                
                $this->info("Deleted {$deletedCount} orphaned files");
                $this->info("Freed up {$this->formatBytes($totalSize)} of storage");
            }
        }

        // Step 3: Check for database records with missing files
        $this->info("\n=== Step 3: Checking database records with missing files ===");
        
        $missingFileRecords = [];
        foreach (PermitPhoto::whereNotNull('file_path')->get() as $photo) {
            $fullPath = public_path($photo->file_path);
            if (!file_exists($fullPath)) {
                $missingFileRecords[] = [
                    'id' => $photo->id,
                    'permit_id' => $photo->permit_id,
                    'file_path' => $photo->file_path
                ];
            }
        }
        
        $this->info("Found " . count($missingFileRecords) . " database records with missing files");
        
        if (count($missingFileRecords) > 0) {
            if ($dryRun) {
                $this->table(['ID', 'Permit ID', 'Missing File Path'], $missingFileRecords);
            } else {
                $deleted = PermitPhoto::whereIn('id', array_column($missingFileRecords, 'id'))->delete();
                $this->info("Deleted {$deleted} database records with missing files");
            }
        }

        $this->info("\n=== Cleanup Complete ===");
        
        if ($dryRun) {
            $this->info("Run without --dry-run to actually delete the orphaned files and records");
        } else {
            $this->info("Cleanup completed successfully");
        }
        
        return 0;
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
