<?php
/**
 * Plugin deactivator.
 *
 * @package WordPress_Speed_Analyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WSA_Deactivator {

	/**
	 * Run plugin deactivation tasks.
	 *
	 * @return void
	 */
	public static function deactivate() {
		self::clear_scheduled_events();
		flush_rewrite_rules();
	}

	/**
	 * Clear scheduled cron events.
	 *
	 * @return void
	 */
	private static function clear_scheduled_events() {

		$timestamp = wp_next_scheduled( 'wsa_run_scheduled_audit' );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'wsa_run_scheduled_audit' );
		}

		// Safety: remove all remaining scheduled hooks if any exist
		wp_clear_scheduled_hook( 'wsa_run_scheduled_audit' );
	}
}