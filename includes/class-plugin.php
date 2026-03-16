<?php
/**
 * Main plugin controller.
 *
 * @package WordPress_Speed_Analyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WSA_Plugin {

	/**
	 * Admin instance.
	 *
	 * @var WSA_Admin
	 */
	private $admin;

	/**
	 * Settings service instance.
	 *
	 * @var WSA_Settings
	 */
	private $settings;

	/**
	 * Scheduler service instance.
	 *
	 * @var WSA_Scheduler
	 */
	private $scheduler;

	/**
	 * PageSpeed API instance.
	 *
	 * @var WSA_PageSpeed_API
	 */
	private $pagespeed_api;

	/**
	 * Suggestions engine instance.
	 *
	 * @var WSA_Suggestions_Engine
	 */
	private $suggestions_engine;

	/**
	 * Plugin impact analyzer instance.
	 *
	 * @var WSA_Plugin_Impact_Analyzer
	 */
	private $plugin_impact_analyzer;

	/**
	 * Database analyzer instance.
	 *
	 * @var WSA_Database_Analyzer
	 */
	private $database_analyzer;

	/**
	 * Audit manager instance.
	 *
	 * @var WSA_Audit_Manager
	 */
	private $audit_manager;

	/**
	 * REST controller instance.
	 *
	 * @var WSA_REST_Controller
	 */
	private $rest_controller;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->setup_services();
	}

	/**
	 * Load dependencies or prepare anything needed before setup.
	 *
	 * Kept minimal for v1 because files are already required in the bootstrap file.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		// Reserved for future dependency setup if needed.
	}

	/**
	 * Create all service instances.
	 *
	 * @return void
	 */
	private function setup_services() {
		$this->settings               = new WSA_Settings();
		$this->scheduler              = new WSA_Scheduler( $this->settings );
		$this->pagespeed_api          = new WSA_PageSpeed_API( $this->settings );
		$this->suggestions_engine     = new WSA_Suggestions_Engine();
		$this->plugin_impact_analyzer = new WSA_Plugin_Impact_Analyzer();
		$this->database_analyzer      = new WSA_Database_Analyzer();

		$this->audit_manager = new WSA_Audit_Manager(
			$this->pagespeed_api,
			$this->suggestions_engine,
			$this->plugin_impact_analyzer,
			$this->database_analyzer,
			$this->settings
		);

		$this->admin = new WSA_Admin(
			$this->settings,
			$this->audit_manager
		);

		$this->rest_controller = new WSA_REST_Controller(
			$this->audit_manager,
			$this->settings
		);
	}

	/**
	 * Run the plugin.
	 *
	 * @return void
	 */
	public function run() {
		$this->register_hooks();
	}

	/**
	 * Register all WordPress hooks.
	 *
	 * @return void
	 */
	private function register_hooks() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'admin_menu', array( $this->admin, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this->admin, 'register_settings' ) );

		add_action( 'rest_api_init', array( $this->rest_controller, 'register_routes' ) );

		add_action( 'init', array( $this->scheduler, 'register_schedules' ) );
		add_action( 'wsa_run_scheduled_audit', array( $this->audit_manager, 'run_scheduled_audit' ) );
	}

	/**
	 * Load plugin translations.
	 *
	 * This is optional here because the bootstrap file already loads textdomain,
	 * but keeping it here makes the class more self-contained.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'wordpress-speed-analyzer',
			false,
			dirname( WSA_BASENAME ) . '/languages'
		);
	}

	/**
	 * Get settings service.
	 *
	 * @return WSA_Settings
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Get audit manager.
	 *
	 * @return WSA_Audit_Manager
	 */
	public function get_audit_manager() {
		return $this->audit_manager;
	}
}