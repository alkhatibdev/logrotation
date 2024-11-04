<?php

namespace AlkhatibDev\LogRotation;

use Carbon\Carbon;

class LogRotator
{
    /**
     * The log file to rotate.
     *
     * @var string
     */
    protected $logFile;

    /**
     * The maximum number of months to keep.
     *
     * @var int
     */
    protected $maxMonths;

    public function __construct()
    { }

    /**
     * Set the log file to rotate.
     *
     * @param  string  $logFile
     * @return void
     */
    public function setLogFile($logFile): LogRotator
    {
        $this->logFile = $logFile;

        return $this;
    }

    /**
     * Get the log file to rotate.
     *
     * @return string
     */
    protected function getLogFile(): string
    {
        if ($this->logFile === null) {
            $this->logFile = storage_path('logs/laravel.log');
        }

        return $this->logFile;
    }

    /**
     * Set the maximum number of months to keep.
     *
     * @param  int  $maxMonths
     * @return void
     */
    public function setMaxMonths($maxMonths): LogRotator
    {
        $this->maxMonths = $maxMonths;

        return $this;
    }

    /**
     * Get the maximum number of months to keep.
     *
     * @return int
     */
    protected function getMaxMonths(): int
    {
        if ($this->maxMonths === null) {
            $this->maxMonths = config('logrotation.max_months', 6);
        }

        return $this->maxMonths;
    }

    /**
     * Rotate the log file.
     *
     * @return void
     */
    public function rotate(): void
    {
        $logFile = $this->getLogFile();

        if (file_exists($logFile)) {
            // Get the creation month that the rotated log file will be created in.
            $fileCreationMonth = Carbon::now()->subMonth()->format('Y-m');
            $newLogFile = dirname($logFile) . '/' . $fileCreationMonth . '-' . basename($logFile);

            // Rotate the log if it's not already rotated
            if (! file_exists($newLogFile)) {
                rename($logFile, $newLogFile);

                // Create a new empty log file
                file_put_contents($logFile, '');

                $this->deleteOldLogs();
            }
        }
    }

    /**
     * Delete old logs that older than max months value.
     *
     * @return void
     */
    protected function deleteOldLogs(): void
    {
        // Get all rotated logs
        $logFiles = glob(dirname($this->getLogFile()) . '/20*');

        // Sort logs by date (newest first)
        usort($logFiles, function($a, $b) {
            return strcmp($b, $a);
        });

        // Keep only the latest $maxMonths logs
        foreach (array_slice($logFiles, $this->getMaxMonths()) as $oldLog) {
            unlink($oldLog);
        }
    }
}
