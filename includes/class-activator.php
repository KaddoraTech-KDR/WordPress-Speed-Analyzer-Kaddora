<?php
/**
 * Plugin activator.
 *
 * @package WordPress_Speed_Analyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WSA_Activator {

	/**
	 * Run plugin activation tasks.
	 *
	 * @return void
	 */
	public static function activate() {
		self::create_reports_table();
		self::add_default_options();
		self::schedule_events();

		flush_rewrite_rules();
	}

	/**
	 * Create the audit reports table.
	 *
	 * @return void
	 */
	private static function create_reports_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'wsa_reports';
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			audit_url text NOT NULL,
			device_type varchar(20) NOT NULL DEFAULT 'mobile',
			performance_score smallint(3) unsigned NOT NULL DEFAULT 0,
			lcp decimal(10,2) DEFAULT NULL,
			inp decimal(10,2) DEFAULT NULL,
			cls decimal(10,4) DEFAULT NULL,
			fcp decimal(10,2) DEFAULT NULL,
			ttfb decimal(10,2) DEFAULT NULL,
			pagespeed_data longtext DEFAULT NULL,
			suggestions_data longtext DEFAULT NULL,
			plugin_impact_data longtext DEFAULT NULL,
			database_analysis_data longtext DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY device_type (device_type),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Add default plugin options.
	 *
	 * @return void
	 */
	private static function add_default_options() {
		$default_options = array(
			'api_key'                => '',
			'target_url'             => home_url( '/' ),
			'default_device'         => 'mobile',
			'scan_frequency'         => 'daily',
			'history_limit'          => 30,
			'delete_data_on_uninstall' => 0,
		);

		$current_options = get_option( 'wsa_settings', array() );

		if ( ! is_array( $current_options ) ) {
			$current_options = array();
		}

		$merged_options = wp_parse_args( $current_options, $default_options );

		add_option( 'wsa_settings', $merged_options );
		update_option( 'wsa_settings', $merged_options );
	}

	/**
	 * Schedule plugin cron events.
	 *
	 * @return void
	 */
	private static function schedule_events() {
		if ( ! wp_next_scheduled( 'wsa_run_scheduled_audit' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'wsa_run_scheduled_audit' );
		}
	}
}