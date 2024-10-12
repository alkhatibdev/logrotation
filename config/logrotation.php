<?php

return [
    /**
     * Max Months to Retain Logs
     * --------------------------------------------------------------------------
     * This value is used to determine the maximum number of months to retain
     * logs. You can change this value to any positive integer to adjust the
     * retention period. For example, if you want to retain logs for a maximum
     * of 6 months, you can set this value to 6.
     */
    'max_months' => env('LOG_ROTATION_MAX_MONTHS', 6),
];
