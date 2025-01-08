# WordPress Cron Registration Library

A comprehensive PHP library for registering and managing WordPress cron jobs and schedules programmatically. This library provides a robust solution for creating and managing WordPress cron jobs with automatic prefixing and schedule management.

## Features

- 🚀 Simple cron job and schedule registration
- 🔄 Custom schedule intervals management
- ⏰ Single and recurring event support
- 🛠️ Simple utility functions for quick implementation
- ✅ Comprehensive error handling
- 🔍 Debug logging support

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher

## Installation

You can install the package via composer:

```bash
composer require arraypress/wp-register-cron
```

## Basic Usage

Here's a simple example of registering custom schedules and cron jobs:

```php
// Register custom schedule intervals
$schedules = [
	'twice_daily'   => [
		'interval' => 12 * HOUR_IN_SECONDS,
		'display'  => 'Twice Daily'
	],
	'every_6_hours' => [
		'interval' => 6 * HOUR_IN_SECONDS,
		'display'  => 'Every 6 Hours'
	]
];

register_cron_schedules( $schedules, 'my_plugin' );

// Register cron jobs
$jobs = [
	'sync_data' => [
		'callback' => 'sync_data_function',
		'schedule' => 'my_plugin_twice_daily',
		'args'     => [ 'param1', 'param2' ]
	],
	'cleanup'   => [
		'callback' => 'cleanup_function',
		'schedule' => false, // Single event
		'start'    => time() + HOUR_IN_SECONDS
	]
];

register_cron_jobs( $jobs, 'my_plugin' );
```

## Configuration Options

### Schedule Configuration

Each schedule can be configured with:

| Option | Type | Description |
|--------|------|-------------|
| interval | int | Time in seconds between runs |
| display | string | Human-readable name for the schedule |

### Job Configuration

Each job can be configured with:

| Option | Type | Description |
|--------|------|-------------|
| callback | callable | Function to execute (required) |
| schedule | string/bool | Schedule name or false for single event |
| start | int | Timestamp for first run (default: time()) |
| args | array | Arguments to pass to callback |

## Utility Functions

Global helper functions for easy access:

```php
// Register custom schedules
register_cron_schedules( $schedules, 'prefix' );

// Register cron jobs
register_cron_jobs( $jobs, 'prefix' );

// Unregister cron jobs
unregister_cron_jobs( $jobs, 'prefix' );
```

## Using the Class Directly

For more advanced usage, you can use the class directly:

```php
use ArrayPress\WP\Register\Cron;

$cron = Cron::instance();

// Add custom schedules
$cron->add_schedules( [
	'custom_interval' => [
		'interval' => 3600,
		'display'  => 'Every Hour'
	]
] );

// Add cron jobs
$cron->add_jobs( [
	'hourly_task' => [
		'callback' => [ $this, 'hourly_function' ],
		'schedule' => 'custom_interval'
	]
] );

// Install everything
$cron->install();
```

## Advanced Example

Here's an example showing more advanced usage:

```php
class MyPlugin {
	public function init() {
		// Register custom schedules
		register_cron_schedules( [
			'every_4_hours' => [
				'interval' => 4 * HOUR_IN_SECONDS,
				'display'  => 'Every 4 Hours'
			]
		], 'my_plugin' );

		// Register various cron jobs
		register_cron_jobs( [
			'data_sync'        => [
				'callback' => [ $this, 'sync_data' ],
				'schedule' => 'my_plugin_every_4_hours',
				'args'     => [ 'full_sync' => false ]
			],
			'daily_report'     => [
				'callback' => [ $this, 'generate_report' ],
				'schedule' => 'daily',
				'start'    => strtotime( 'tomorrow 1am' )
			],
			'one_time_cleanup' => [
				'callback' => [ $this, 'perform_cleanup' ],
				'schedule' => false, // Single event
				'start'    => time() + DAY_IN_SECONDS
			]
		], 'my_plugin' );
	}

	public function deactivate() {
		// Clean up cron jobs on deactivation
		unregister_cron_jobs( [
			'data_sync',
			'daily_report',
			'one_time_cleanup'
		], 'my_plugin' );
	}
}
```

## Debug Mode

Debug logging is enabled when WP_DEBUG is true:

```php
// Logs will include:
// - Schedule registration
// - Job scheduling
// - Invalid configurations
// - Job removals
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

## License

This project is licensed under the GPL2+ License. See the LICENSE file for details.

## Support

For support, please use the [issue tracker](https://github.com/arraypress/wp-register-cron/issues).