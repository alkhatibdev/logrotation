<?php
namespace AlkhatibDev\LogRotation;

use Carbon\Carbon;

class LogRotator
{
    protected $logFile;
    protected $maxMonths;

    /**
     * LogRotator constructor.
     *
     * @param string $logFile
     */
    public function __construct($logFile = null)
    {
        $this->logFile   = $logFile ?? storage_path('logs/laravel.log');
        $this->maxMonths = config('logrotation.max_months', 6);
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
     * Rotate the log file
     *
     * @return void
     */
    public function rotate(): void
    {
        if (file_exists($this->logFile)) {
            // Get the creation month, It's the previous month
            $fileCreationMonth = Carbon::now()->subMonth()->format('Y-m');
            $newLogFile        = dirname($this->logFile) . '/' . $fileCreationMonth . '-' . basename($this->logFile);

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

    /**
     * Delete old logs
     *
     * @return void
     */
    protected function deleteOldLogs(): void
    {
        // Get all rotated logs
        $logFiles = glob(dirname($this->logFile) . '/20*');

        // Sort logs by date (newest first)
        usort($logFiles, function ($a, $b) {
            return strcmp($b, $a);
        });

        // Keep only the latest $maxMonths logs
        foreach (array_slice($logFiles, $this->maxMonths) as $oldLog) {
            unlink($oldLog);
        }
    }
}
