<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();
        date_default_timezone_set('Europe/Paris');
    }

    private function checkDatabaseConfiguration(): void
    {
        $databaseUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? null;
        
        if (!$databaseUrl) {
            return; // No DATABASE_URL set, skip check
        }

        // Only check in production to avoid warnings in dev
        if ($this->getEnvironment() === 'dev') {
            return;
        }

        // Check if using SQLite
        if (strpos($databaseUrl, 'sqlite://') === 0) {
            // Extract file path from DATABASE_URL
            $path = str_replace('sqlite:///', '', $databaseUrl);
            $path = str_replace('%kernel.project_dir%', $this->getProjectDir(), $path);
            
            // Skip check if using /tmp (local filesystem)
            if (strpos($path, '/tmp/') === 0) {
                return;
            }
            
            if (file_exists($path)) {
                // Check if the file or its parent directory is on NFS
                $realPath = realpath($path);
                if ($realPath === false) {
                    $realPath = realpath(dirname($path));
                }
                
                if ($realPath) {
                    $fsType = $this->detectFileSystemType($realPath);
                    
                    if (strpos($fsType, 'nfs') !== false) {
                        throw new \RuntimeException(
                            "❌ FATAL: SQLite database on NFS mount detected!\n\n" .
                            "Database path: {$path}\n" .
                            "Filesystem type: {$fsType}\n\n" .
                            "SQLite does NOT work reliably on NFS (causes 'disk I/O error' and lock failures).\n\n" .
                            "✅ SOLUTIONS:\n\n" .
                            "1. USE DOCKER + POSTGRES (Recommended for all machines):\n" .
                            "   docker compose up -d\n" .
                            "   This starts PostgreSQL automatically and is portable across all PCs.\n\n" .
                            "2. USE LOCAL FILESYSTEM for SQLite:\n" .
                            "   In .env.local, set:\n" .
                            "   DATABASE_URL=\"sqlite:////tmp/groloto_local.db\"\n" .
                            "   (This only works on the current machine and must be repeated for each PC)\n\n" .
                            "RECOMMENDED: Use 'docker compose up -d' for a solution that works everywhere.\n"
                        );
                    }
                }
            }
        }
    }

    private function detectFileSystemType(string $path): string
    {
        // Use 'df -T' to detect filesystem type (Linux)
        if (PHP_OS_FAMILY !== 'Linux') {
            return 'unknown';
        }

        $output = shell_exec("df -T " . escapeshellarg($path) . " 2>/dev/null | tail -1");
        if (!$output) {
            return 'unknown';
        }

        $parts = preg_split('/\s+/', trim($output));
        return isset($parts[1]) ? $parts[1] : 'unknown';
    }
}
