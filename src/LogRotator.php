<?php
namespace AlkhatibDev\LogRotation;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LogRotator
{
    protected string $logFile;
    protected int $maxMonths;
    protected ?int $maxSizeKb;
    protected bool $compressArchived;

    /**
     * LogRotator constructor.
     *
     * @param string $logFile
     */
    public function __construct($logFile = null)
    {
        $this->logFile          = $logFile ?? storage_path('logs/laravel.log');
        $this->maxMonths        = config('logrotation.max_months', 6);
        $this->maxSizeKb        = config('logrotation.max_size_kb', null);
        $this->compressArchived = config('logrotation.compress_archived', false);
    }

    /**
     * Create a new LogRotator instance
     *
     * @return self
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Set the log file path
     *
     * @param string $logFile
     * @return self
     */
    public function setLogFile(string $logFile): self
    {
        $this->logFile = $logFile;
        return $this;
    }

    /**
     * Set maximum months to retain logs
     *
     * @param int $months
     * @return self
     */
    public function setMaxMonths(int $months): self
    {
        $this->maxMonths = $months;
        return $this;
    }

    /**
     * Set maximum size in KB before rotation
     * If null, no size based rotation will be performed
     *
     * @param int $sizeKb
     * @return self
     */
    public function setMaxSize(?int $sizeKb): self
    {
        $this->maxSizeKb = $sizeKb;
        return $this;
    }

    /**
     * Enable or disable compression for archived logs
     */
    public function setCompression(bool $compress): self
    {
        $this->compressArchived = $compress;
        return $this;
    }

    /**
     * Rotate the log file
     *
     * @return void
     */
    public function rotate(): void
    {
        if (! File::exists($this->logFile)) {
            return;
        }

        $this->rotateByTimeAndArchive();

        if ($this->maxSizeKb !== null && $this->shouldRotateBySize()) {
            $this->rotateBySizeAndArchive();
        }

        $this->deleteOldLogArchives();
    }

    /**
     * Check if file size exceeds the threshold
     */
    protected function shouldRotateBySize(): bool
    {
        $fileSizeKb = File::size($this->logFile) / 1024;
        return $fileSizeKb >= $this->maxSizeKb;
    }

    /**
     * Rotate log by size and create archive
     */
    protected function rotateBySizeAndArchive(): void
    {
        try {
            $timestamp   = Carbon::now()->format('Y-m_d-H-i-s');
            $archivePath = $this->getArchivePath($timestamp, 'size');

            File::copy($this->logFile, $archivePath);

            if ($this->compressArchived) {
                $this->compressFile($archivePath);
            }

            File::put($this->logFile, '');

        } catch (\Exception $e) {
            Log::error("Failed to rotate log by size: {$e->getMessage()}", [
                'file' => $this->logFile,
            ]);
        }
    }

    /**
     * Rotate log by time and create archive
     */
    protected function rotateByTimeAndArchive(): void
    {
        try {
            $timestamp   = Carbon::now()->subMonth()->format('Y-m');
            $archivePath = $this->getArchivePath($timestamp, 'monthly');

            if (File::exists($archivePath) || File::exists($archivePath . '.gz')) {
                return;
            }

            File::copy($this->logFile, $archivePath);

            if ($this->compressArchived) {
                $this->compressFile($archivePath);
            }

            File::put($this->logFile, '');

        } catch (\Exception $e) {
            Log::error("Failed to rotate log by time: {$e->getMessage()}", [
                'file' => $this->logFile,
            ]);
        }
    }

    /**
     * Generate archive file path
     */
    protected function getArchivePath(string $timestamp, string $type): string
    {
        $logDirectory = dirname($this->logFile);
        $logBaseName  = pathinfo($this->logFile, PATHINFO_FILENAME);

        return "{$logDirectory}/{$logBaseName}-{$timestamp}-{$type}.log";
    }

    /**
     * Compress a file using gzip
     *
     * @param string $filePath
     * @return void
     */
    protected function compressFile(string $filePath): void
    {
        if (! function_exists('gzencode')) {
            return;
        }

        try {
            $content    = File::get($filePath);
            $compressed = gzencode($content, 9);

            File::put($filePath . '.gz', $compressed);
            File::delete($filePath);

        } catch (\Exception $e) {
            Log::error("Failed to compress log file: {$e->getMessage()}", [
                'file' => $filePath,
            ]);
        }
    }

    /**
     * Delete old logs
     *
     * @return void
     */
    protected function deleteOldLogArchives(): void
    {
        $keepMonths = $this->maxMonths;
        $cutoffDate = now()->subMonths($keepMonths)->startOfMonth();

        $files       = File::files(File::dirname($this->logFile));
        $logBaseName = pathinfo($this->logFile, PATHINFO_FILENAME);

        foreach ($files as $file) {
            try {
                $filename = $file->getFilename();
                if (preg_match('/' . $logBaseName . '-(\d{4})-(\d{2}).*-(monthly|size)\.log(\.gz)?$/', $filename, $matches)) {
                    [$full, $year, $month] = $matches;
                    $fileDate              = Carbon::createFromDate($year, $month, 1)->startOfMonth();

                    if ($fileDate->lt($cutoffDate)) {
                        File::delete($file->getRealPath());
                    }
                }
            } catch (\Exception $e) {
                Log::error("Log rotation failed to delete old log archive: {$e->getMessage()}", [
                    'file' => $file,
                ]);
            }
        }
    }
}
