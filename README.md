# Laravel Log Rotation Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alkhatibdev/logrotation.svg?style=flat-square)](https://packagist.org/packages/alkhatibdev/logrotation)
[![Total Downloads](https://img.shields.io/packagist/dt/alkhatibdev/logrotation.svg?style=flat-square)](https://packagist.org/packages/alkhatibdev/logrotation)
[![GitHub Issues](https://img.shields.io/github/issues/alkhatibdev/logrotation.svg?style=flat-square)](https://github.com/alkhatibdev/logrotation/issues)

This package makes log file rotation easier by automatically managing and organizing Laravel logs based on file creation date, retaining only the most recent logs (e.g., 6 months) and discarding older logs. This solution is ideal for applications that generate high volumes of logs, providing efficient log management and preventing excessive disk usage over time.

## Benefits

- Rotates your log files based on the log file's current date, ensuring that only the most recent logs are retained.
- Retain logs for a configurable number of months (default: 6 months), which can easily be modified through the configuration file.
- Automatically deletes old logs after the configured retention period, freeing up space on your server.

## Installation

Install the package via Composer:

```bash
composer require alkhatibdev/logrotation
```

## Configuration

To publish the configuration file, run the following command:

```bash
php artisan vendor:publish --tag=logrotation
```

This will create a `logrotation.php` file in your `config/` directory where you can customize the number of months to retain logs:

```php
return [
    'max_months' => env('LOG_ROTATION_MAX_MONTHS', 6),
];
```

## Usage

Once installed, you can integrate the log rotation into your application's task scheduling. To rotate logs on a monthly basis, open your `app/Console/Kernel.php` file and add the following to the `schedule()` method:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        app('logrotator')->rotate();
    })->monthly();
}
```
For **Laravel 11.x**, You can create scheduled command on `routes/console.php` file:
```php
Artisan::command('logrotation:rotate', function () {
    app('logrotator')->rotate();
})->monthly();
```

This will ensure that logs are rotated and older logs are deleted automatically at the beginning of every month.

## Manual Log Rotation

You can also trigger log rotation manually by running:

```bash
php artisan schedule:run
```

This will immediately check and rotate logs if necessary.

## Advanced Customization

In case you want to change the default log location or customize the log retention behavior, you can extend the `LogRotator` class and override its methods. By default, the package manages the `storage/logs/laravel.log` file, but you can pass a custom log file path when initializing the class:

```php
use AlkhatibDev\LogRotation\LogRotator;

$logRotator = new LogRotator();
$logRotator
    ->setLogFile(storage_path('logs/custom.log')) // Set the log file path to rotate
    ->rotate();
```

## Support

If you encounter any issues or have feature requests, feel free to [open an issue](https://github.com/alkhatibdev/logrotation/issues) on GitHub.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
