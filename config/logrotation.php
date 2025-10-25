<?php

return [

    /**
     * Maximum Months to Retain Logs
     * --------------------------------------------------------------------------
     * This value determines how many months of log files should be retained.
     * Logs older than this will be automatically deleted during rotation.
     */
    'max_months' => env('LOG_ROTATION_MAX_MONTHS', 12),

    /*
    |--------------------------------------------------------------------------
    | Maximum File Size (KB)
    |--------------------------------------------------------------------------
    |
    | When the log file reaches this size in kilobytes, it will be rotated
    | automatically regardless of the time-based schedule. Set to null to
    | disable size-based rotation.
    |
    | Example: 10240 = 10MB, 51200 = 50MB
    |
    */
    'max_size_kb' => env('LOG_ROTATION_MAX_SIZE_KB', null),

    /**
     * Compress Archived Logs
     * --------------------------------------------------------------------------
     * When enabled, archived log files will be compressed using gzip to save
     * disk space. The original log file will be replaced with a .gz version.
     */
    'compress_archived' => env('LOG_ROTATION_COMPRESS', true),
    
];
