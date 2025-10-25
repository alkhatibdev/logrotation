# Laravel Log Rotation Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alkhatibdev/logrotation.svg?style=flat-square)](https://packagist.org/packages/alkhatibdev/logrotation)
[![Total Downloads](https://img.shields.io/packagist/dt/alkhatibdev/logrotation.svg?style=flat-square)](https://packagist.org/packages/alkhatibdev/logrotation)
[![GitHub Issues](https://img.shields.io/github/issues/alkhatibdev/logrotation.svg?style=flat-square)](https://github.com/alkhatibdev/logrotation/issues)

This package makes log file rotation easier by automatically managing and organizing Laravel logs based on **time and file size**, retaining only the most recent logs (e.g., 6 months) and discarding older logs. This solution is ideal for applications that generate high volumes of logs, providing efficient log management and preventing excessive disk usage over time.

## Benefits

- Rotates your log files **based on date** (monthly) or **file size** (size-based rotation).
- Retains logs for a configurable number of months (default: 6 months), which can be modified through the configuration file.
- Automatically deletes old logs after the configured retention period, freeing up disk space.
- Supports both **compressed (`.gz`)** and uncompressed log files.
- Helps prevent large log files from consuming excessive disk space during high-traffic periods.

## Installation

Install the package via Composer:

```bash
composer require alkhatibdev/logrotation
```

## Configuration

To customize the log rotation settings, you can set the following values in your **`.env` file**:

```env
# Number of months to retain logs (default: 12)
LOG_ROTATION_MAX_MONTHS=12

# Maximum file size (in KB) for size-based rotation (optional, default: null, no size-based rotation)
LOG_ROTATION_MAX_SIZE_KB=10240

# Enable or disable compression for archived logs (optional, default: true)
LOG_ROTATION_COMPRESS=true
```

* `LOG_ROTATION_MAX_MONTHS`: How many months of logs to keep before deletion.
* `LOG_ROTATION_MAX_SIZE_KB`: The size threshold for size-based rotation. When the log file exceeds this, a size-based archive is created.
* `LOG_ROTATION_COMPRESS`: Whether the archived logs should be compressed (`.gz`) or kept uncompressed (`.log`).

**Note:** You can still publish the configuration file if you want to override defaults or use dynamic configuration:

```bash
php artisan vendor:publish --tag=logrotation
```

## Usage

### Log Rotation Scheduling

You can schedule log rotation in `routes/console.php`:
```php
Artisan::command('logrotation:rotate', function () {
    app('logrotator')->rotate();
})->daily();
```

Or for **Laravel 10.x** and below, in `routes/console.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        app('logrotator')->rotate();
    })->daily();
}
```

**How it works:**

* Scheduling **daily** ensures **size-based rotation** works properly, rotating large log files as soon as they exceed the configured size.
* Monthly rotation will still run **only once per month** — if the current month has already been rotated, it will be ignored until the next month.
* **Optional:** If you don’t want to enable size-based rotation, you can run the command `monthly()` instead. This will rotate logs **only once per month** based on time, ignoring size increasing during the month.


### Monthly Log Archives

* The rotation creates a **timestamped archive** for the current file:

```
file-YYYY-MM-monthly.log
file-YYYY-MM-monthly.log.gz (if compression is enabled)
```

### Size-Based Log Rotation

The package now supports automatic rotation **when the log file exceeds a specified size**. This is useful for high-traffic applications where a single month’s log may grow too large.

* The rotation creates a **timestamped archive** for the current file:

```
file-YYYY-MM_DD-HH-MM-SS-size.log
file-YYYY-MM_DD-HH-MM-SS-size.log.gz (if compression is enabled)
```

* Size-based logs are also subject to **month-based retention** and will be deleted if their month is older than the configured `max_months`.

<!-- ## Manual Log Rotation

You can manually trigger log rotation at any time:

```bash
php artisan logrotation:rotate
```

This will check the logs and rotate them immediately if needed (time or size-based).
 -->

## Advanced Customization

You can override the default log file location or retention behavior by extending the `LogRotator` class:

```php
use AlkhatibDev\LogRotation\LogRotator;

LogRotator::make()
    ->setLogFile(storage_path('logs/custom.log'))
    ->setMaxMonths(12)
    ->setMaxSize(10240)
    ->setCompression(true)
    ->rotate();
```

The package manages both `monthly` and `size` rotation automatically.

## Support

If you encounter any issues or have feature requests, feel free to [open an issue](https://github.com/alkhatibdev/logrotation/issues) on GitHub.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
