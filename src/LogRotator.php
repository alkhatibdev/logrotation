<?php

namespace AlkhatibDev\LogRotation;

use Carbon\Carbon;

class LogRotator
{
    protected $logFile;
    protected $maxMonths;

    public function __construct($logFile = null)
    {
        // Default log file location
        $this->logFile = $logFile ?? storage_path('logs/laravel.log');
        $this->maxMonths = config('logrotation.max_months', 6);
    }

    public function rotate(): void
    {
        // If the log file exists
        if (file_exists($this->logFile)) {
            // Get the creation month, It's the previous month
            $fileCreationMonth = Carbon::now()->subMonth()->format('Y-m');
            $newLogFile = dirname($this->logFile) . '/' . $fileCreationMonth . '-' . basename($this->logFile);

            // Rotate the log if it's not already rotated
            if (! file_exists($newLogFile)) {
                rename($this->logFile, $newLogFile);

                // Create a new empty log file
                file_put_contents($this->logFile, '');

                // Rotate old logs
                $this->deleteOldLogs();
            }
        }
    }

    protected function deleteOldLogs(): void
    {
        // Get all rotated logs
        $logFiles = glob(dirname($this->logFile) . '/20*');

        // Sort logs by date (newest first)
        usort($logFiles, function($a, $b) {
            return strcmp($b, $a);
        });

        // Keep only the latest $maxMonths logs
        foreach (array_slice($logFiles, $this->maxMonths) as $oldLog) {
            unlink($oldLog);
        }
    }
}