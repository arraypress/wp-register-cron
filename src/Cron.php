<?php
/**
 * Cron Registration Manager
 *
 * A comprehensive solution for managing WordPress cron jobs with features like:
 * - Plugin-specific job management
 * - Custom schedule registration
 * - Job installation and uninstallation
 * - Error logging and debugging
 *
 * @package     ArrayPress\WP\Register
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\WP\Register;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use InvalidArgumentException;

/**
 * Class Cron
 *
 * Manages WordPress cron job registration and scheduling.
 *
 * @package ArrayPress\WP\Register
 * @since   1.0.0
 */
class Cron {

	/**
	 * Collection of class instances
	 *
	 * @since 1.0.0
	 * @var self[] Array of instances, keyed by plugin basename
	 */
	private static array $instances = [];

	/**
	 * Plugin file path
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $plugin_file = '';

	/**
	 * Plugin basename
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $basename = '';

	/**
	 * Collection of schedules to be registered
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $schedules = [];

	/**
	 * Collection of cron jobs to be registered
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $jobs = [];

	/**
	 * Option prefix for storing cron data
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $prefix = '';

	/**
	 * Debug mode status
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private bool $debug = false;

	/**
	 * Get instance for a plugin
	 *
	 * @param string $plugin_file Plugin file path
	 *
	 * @return self Instance of this class
	 * @throws InvalidArgumentException If plugin file path is empty
	 * @since  1.0.0
	 */
	public static function instance( string $plugin_file ): self {
		if ( empty( $plugin_file ) ) {
			throw new InvalidArgumentException( 'Plugin file path must be provided.' );
		}

		$basename = plugin_basename( $plugin_file );

		if ( ! isset( self::$instances[ $basename ] ) ) {
			self::$instances[ $basename ] = new self( $plugin_file );
		}

		return self::$instances[ $basename ];
	}

	/**
	 * Constructor
	 *
	 * @param string $plugin_file Plugin file path
	 *
	 * @since 1.0.0
	 */
	private function __construct( string $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->basename    = plugin_basename( $plugin_file );
		$this->debug       = defined( 'WP_DEBUG' ) && WP_DEBUG;

		add_filter( 'cron_schedules', [ $this, 'add_custom_schedules' ] );
	}

	/**
	 * Set the prefix
	 *
	 * @param string $prefix The prefix to use
	 *
	 * @return self
	 * @since  1.0.0
	 */
	public function set_prefix( string $prefix ): self {
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * Add custom schedules
	 *
	 * @param array $schedules Array of WordPress schedules
	 *
	 * @return array Modified schedules array
	 * @since  1.0.0
	 */
	public function add_custom_schedules( array $schedules ): array {
		return array_merge( $schedules, $this->schedules );
	}

	/**
	 * Add schedules to be registered
	 *
	 * @param array $schedules Array of schedules
	 *
	 * @return self
	 * @since  1.0.0
	 */
	public function add_schedules( array $schedules ): self {
		foreach ( $schedules as $name => $schedule ) {
			$this->add_schedule( $name, $schedule );
		}

		return $this;
	}

	/**
	 * Add a single schedule
	 *
	 * @param string $name     Schedule name
	 * @param array  $schedule Schedule configuration
	 *
	 * @return self
	 * @since  1.0.0
	 */
	public function add_schedule( string $name, array $schedule ): self {
		if ( ! $this->is_valid_name( $name ) ) {
			$this->log( sprintf( 'Invalid schedule name: %s', $name ) );

			return $this;
		}

		if ( ! isset( $schedule['interval'] ) || ! is_numeric( $schedule['interval'] ) ) {
			$this->log( sprintf( 'Invalid interval for schedule: %s', $name ) );

			return $this;
		}

		if ( ! isset( $schedule['display'] ) ) {
			$this->log( sprintf( 'Missing display name for schedule: %s', $name ) );

			return $this;
		}

		$prefixed_name                     = $this->maybe_prefix_name( $name );
		$this->schedules[ $prefixed_name ] = [
			'interval' => (int) $schedule['interval'],
			'display'  => $schedule['display']
		];

		return $this;
	}

	/**
	 * Add cron jobs to be registered
	 *
	 * @param array $jobs Array of cron jobs
	 *
	 * @return self
	 * @since  1.0.0
	 */
	public function add_jobs( array $jobs ): self {
		foreach ( $jobs as $hook => $job ) {
			$this->add_job( $hook, $job );
		}

		return $this;
	}

	/**
	 * Add a single cron job
	 *
	 * @param string $hook Hook name
	 * @param array  $job  Job configuration
	 *
	 * @return self
	 * @since  1.0.0
	 */
	public function add_job( string $hook, array $job ): self {
		if ( ! $this->is_valid_name( $hook ) ) {
			$this->log( sprintf( 'Invalid hook name: %s', $hook ) );

			return $this;
		}

		if ( ! isset( $job['callback'] ) || ! is_callable( $job['callback'] ) ) {
			$this->log( sprintf( 'Invalid callback for job: %s', $hook ) );

			return $this;
		}

		$prefixed_hook                = $this->maybe_prefix_name( $hook );
		$this->jobs[ $prefixed_hook ] = wp_parse_args( $job, [
			'callback' => null,
			'schedule' => false, // false for single event, string for recurring
			'start'    => time(),
			'args'     => []
		] );

		return $this;
	}

	/**
	 * Install cron jobs
	 *
	 * @return bool Success status
	 * @since  1.0.0
	 */
	public function install(): bool {
		if ( empty( $this->jobs ) ) {
			return false;
		}

		foreach ( $this->jobs as $hook => $job ) {
			// Add the action hook
			add_action( $hook, $job['callback'] );

			// Schedule the event if it's not already scheduled
			if ( ! wp_next_scheduled( $hook, $job['args'] ) ) {
				if ( $job['schedule'] ) {
					wp_schedule_event(
						$job['start'],
						$job['schedule'],
						$hook,
						$job['args']
					);
				} else {
					wp_schedule_single_event(
						$job['start'],
						$hook,
						$job['args']
					);
				}
				$this->log( sprintf( 'Scheduled cron job: %s', $hook ) );
			}
		}

		$this->store_installation_flag();

		return true;
	}

	/**
	 * Uninstall cron jobs
	 *
	 * @return bool Success status
	 * @since  1.0.0
	 */
	public function uninstall(): bool {
		foreach ( $this->jobs as $hook => $job ) {
			$timestamp = wp_next_scheduled( $hook, $job['args'] );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook, $job['args'] );
				$this->log( sprintf( 'Unscheduled cron job: %s', $hook ) );
			}

			remove_action( $hook, $job['callback'] );
		}

		$this->delete_installation_flag();

		return true;
	}

	/**
	 * Store installation flag
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function store_installation_flag(): void {
		update_option( $this->get_option_key( 'cron_installed' ), true );
	}

	/**
	 * Delete installation flag
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function delete_installation_flag(): void {
		delete_option( $this->get_option_key( 'cron_installed' ) );
	}

	/**
	 * Validate a name (hook or schedule)
	 *
	 * @param string $name Name to validate
	 *
	 * @return bool Whether the name is valid
	 * @since  1.0.0
	 */
	protected function is_valid_name( string $name ): bool {
		return (bool) preg_match( '/^[a-z0-9_-]+$/', $name );
	}

	/**
	 * Maybe prefix a name
	 *
	 * @param string $name Name to maybe prefix
	 *
	 * @return string Possibly prefixed name
	 * @since  1.0.0
	 */
	protected function maybe_prefix_name( string $name ): string {
		if ( empty( $this->prefix ) ) {
			return $this->basename . '_' . $name;
		}

		return $this->prefix . '_' . $name;
	}

	/**
	 * Get prefixed option key
	 *
	 * @param string $key Option key
	 *
	 * @return string Prefixed option key
	 * @since  1.0.0
	 */
	protected function get_option_key( string $key ): string {
		if ( empty( $this->prefix ) ) {
			return $this->basename . '_' . $key;
		}

		return $this->prefix . '_' . $key;
	}

	/**
	 * Log debug message
	 *
	 * @param string $message Message to log
	 * @param array  $context Optional context
	 *
	 * @return void
	 * @since  1.0.0
	 */
	protected function log( string $message, array $context = [] ): void {
		if ( $this->debug ) {
			$identifier = empty( $this->prefix ) ? $this->basename : $this->prefix;
			error_log( sprintf(
				'[%s] Cron: %s %s',
				$identifier,
				$message,
				$context ? json_encode( $context ) : ''
			) );
		}
	}

	/**
	 * Helper method to register cron schedules and jobs
	 *
	 * @param string $plugin_file Plugin file path
	 * @param array  $schedules   Array of schedules to register
	 * @param array  $jobs        Array of jobs to register
	 * @param string $prefix      Optional prefix
	 *
	 * @return bool Success status
	 * @since  1.0.0
	 */
	public static function register(
		string $plugin_file,
		array $schedules = [],
		array $jobs = [],
		string $prefix = ''
	): bool {
		try {
			return self::instance( $plugin_file )
			           ->set_prefix( $prefix )
			           ->add_schedules( $schedules )
			           ->add_jobs( $jobs )
			           ->install();
		} catch ( InvalidArgumentException $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( 'Cron registration failed: %s', $e->getMessage() ) );
			}

			return false;
		}
	}

	/**
	 * Helper method to unregister cron jobs
	 *
	 * @param string $plugin_file Plugin file path
	 * @param array  $schedules   Array of schedules to unregister
	 * @param array  $jobs        Array of jobs to unregister
	 * @param string $prefix      Optional prefix
	 *
	 * @return bool Success status
	 * @since  1.0.0
	 */
	public static function unregister(
		string $plugin_file,
		array $schedules = [],
		array $jobs = [],
		string $prefix = ''
	): bool {
		try {
			return self::instance( $plugin_file )
			           ->set_prefix( $prefix )
			           ->add_schedules( $schedules )
			           ->add_jobs( $jobs )
			           ->uninstall();
		} catch ( InvalidArgumentException $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( 'Cron unregistration failed: %s', $e->getMessage() ) );
			}

			return false;
		}
	}
}