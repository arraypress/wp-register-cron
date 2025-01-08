<?php
/**
 * Cron Registration Helper Functions
 *
 * @package     ArrayPress\WP\Register
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

use ArrayPress\WP\Register\Cron;

if ( ! function_exists( 'register_cron_schedules' ) ):
	/**
	 * Helper function to register custom WordPress cron schedules.
	 *
	 * Example usage:
	 * ```php
	 * // Define custom schedules
	 * $schedules = [
	 *     'twice_daily' => [
	 *         'interval' => 12 * HOUR_IN_SECONDS,
	 *         'display'  => 'Twice Daily'
	 *     ],
	 *     'every_6_hours' => [
	 *         'interval' => 6 * HOUR_IN_SECONDS,
	 *         'display'  => 'Every 6 Hours'
	 *     ]
	 * ];
	 *
	 * // Register schedules with a prefix
	 * register_cron_schedules($schedules, 'my_plugin');
	 * ```
	 *
	 * @param array  $schedules Array of schedules to register
	 * @param string $prefix    Optional prefix for schedule names
	 *
	 * @return bool True on success, false on failure
	 */
	function register_cron_schedules( array $schedules, string $prefix = '' ): bool {
		return Cron::instance()
		           ->set_prefix( $prefix )
		           ->add_schedules( $schedules )
		           ->install();
	}
endif;

if ( ! function_exists( 'register_cron_jobs' ) ):
	/**
	 * Helper function to register WordPress cron jobs.
	 *
	 * Example usage:
	 * ```php
	 * // Define cron jobs
	 * $jobs = [
	 *     'sync_data' => [
	 *         'callback' => 'sync_data_function',
	 *         'schedule' => 'twice_daily',
	 *         'args'     => ['param1', 'param2']
	 *     ],
	 *     'cleanup' => [
	 *         'callback' => 'cleanup_function',
	 *         'schedule' => false, // Single event
	 *         'start'    => time() + HOUR_IN_SECONDS
	 *     ]
	 * ];
	 *
	 * // Register jobs with a prefix
	 * register_cron_jobs($jobs, 'my_plugin');
	 * ```
	 *
	 * @param array  $jobs   Array of jobs to register
	 * @param string $prefix Optional prefix for job hooks
	 *
	 * @return bool True on success, false on failure
	 */
	function register_cron_jobs( array $jobs, string $prefix = '' ): bool {
		return Cron::register( [], $jobs, $prefix );
	}
endif;

if ( ! function_exists( 'unregister_cron_jobs' ) ):
	/**
	 * Helper function to unregister WordPress cron jobs.
	 *
	 * @param array  $jobs   Array of jobs to unregister
	 * @param string $prefix Optional prefix used during registration
	 *
	 * @return bool True on success, false on failure
	 */
	function unregister_cron_jobs( array $jobs, string $prefix = '' ): bool {
		return Cron::unregister( [], $jobs, $prefix );
	}
endif;