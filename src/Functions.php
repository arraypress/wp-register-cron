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
	 * // Register schedules
	 * register_cron_schedules(__FILE__, $schedules, 'my_plugin');
	 * ```
	 *
	 * @param string $plugin_file Plugin file path
	 * @param array  $schedules   Array of schedules to register
	 * @param string $prefix      Optional prefix for schedule names
	 *
	 * @return bool True on success, false on failure
	 */
	function register_cron_schedules( string $plugin_file, array $schedules, string $prefix = '' ): bool {
		return Cron::register( $plugin_file, $schedules, [], $prefix );
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
	 * // Register jobs
	 * register_cron_jobs(__FILE__, $jobs, 'my_plugin');
	 * ```
	 *
	 * @param string $plugin_file Plugin file path
	 * @param array  $jobs        Array of jobs to register
	 * @param string $prefix      Optional prefix for job hooks
	 *
	 * @return bool True on success, false on failure
	 */
	function register_cron_jobs( string $plugin_file, array $jobs, string $prefix = '' ): bool {
		return Cron::register( $plugin_file, [], $jobs, $prefix );
	}
endif;

if ( ! function_exists( 'unregister_cron_jobs' ) ):
	/**
	 * Helper function to unregister WordPress cron jobs.
	 *
	 * Example usage:
	 * ```php
	 * // Unregister jobs
	 * unregister_cron_jobs(__FILE__, $jobs, 'my_plugin');
	 * ```
	 *
	 * @param string $plugin_file Plugin file path
	 * @param array  $jobs        Array of jobs to unregister
	 * @param string $prefix      Optional prefix used during registration
	 *
	 * @return bool True on success, false on failure
	 */
	function unregister_cron_jobs( string $plugin_file, array $jobs, string $prefix = '' ): bool {
		return Cron::unregister( $plugin_file, [], $jobs, $prefix );
	}
endif;

if ( ! function_exists( 'register_cron' ) ):
	/**
	 * Helper function to register both schedules and jobs in one call.
	 *
	 * Example usage:
	 * ```php
	 * // Define schedules and jobs
	 * $schedules = [
	 *     'twice_daily' => [
	 *         'interval' => 12 * HOUR_IN_SECONDS,
	 *         'display'  => 'Twice Daily'
	 *     ]
	 * ];
	 *
	 * $jobs = [
	 *     'sync_data' => [
	 *         'callback' => 'sync_data_function',
	 *         'schedule' => 'twice_daily'
	 *     ]
	 * ];
	 *
	 * // Register both schedules and jobs
	 * register_cron(__FILE__, $schedules, $jobs, 'my_plugin');
	 * ```
	 *
	 * @param string $plugin_file Plugin file path
	 * @param array  $schedules   Array of schedules to register
	 * @param array  $jobs        Array of jobs to register
	 * @param string $prefix      Optional prefix for hooks and schedules
	 *
	 * @return bool True on success, false on failure
	 */
	function register_cron( string $plugin_file, array $schedules, array $jobs, string $prefix = '' ): bool {
		return Cron::register( $plugin_file, $schedules, $jobs, $prefix );
	}
endif;