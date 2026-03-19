<?php

if (!defined('ABSPATH')) {
  exit;
}

class WSA_Admin
{

  private $settings;
  private $audit_manager;

  public function __construct($settings, $audit_manager)
  {
    $this->settings = $settings;
    $this->audit_manager = $audit_manager;
  }

  /**
   * Register admin menu
   */
  public function register_admin_menu()
  {
    add_menu_page(
      __('Speed Analyzer', 'wordpress-speed-analyzer'),
      __('Speed Analyzer', 'wordpress-speed-analyzer'),
      'manage_options',
      'wsa-dashboard',
      [$this, 'render_dashboard'],
      'dashicons-performance',
      3
    );

    add_submenu_page(
      'wsa-dashboard',
      __('Settings', 'wordpress-speed-analyzer'),
      __('Settings', 'wordpress-speed-analyzer'),
      'manage_options',
      'wsa-settings',
      array($this, 'render_settings_page')
    );
  }

  // render_settings_page
  public function render_settings_page()
  {
    require_once WSA_PATH . "templates/settings.php";
  }

  /**
   * Render dashboard page
   */
  public function render_dashboard()
  {
    include WSA_PATH . 'templates/dashboard.php';
  }

  /**
   * Load admin assets
   */
  public function enqueue_assets($hook)
  {
    // Only load on our page
    if ($hook !== 'toplevel_page_wsa-dashboard') {
      return;
    }

    wp_enqueue_style(
      'wsa-admin',
      WSA_URL . 'assets/css/admin.css',
      [],
      WSA_VERSION
    );

    wp_enqueue_script(
      'wsa-dashboard',
      WSA_URL . 'assets/js/dashboard.js',
      ['jquery', 'jspdf'],
      WSA_VERSION,
      true
    );

    wp_localize_script(
      'wsa-dashboard',
      'wsaData',
      [
        'nonce' => wp_create_nonce('wp_rest'),
        'restUrl' => rest_url('wsa/v1/run-audit')
      ]
    );

    wp_enqueue_script(
      'chart-js',
      'https://cdn.jsdelivr.net/npm/chart.js@4.4.0',
      array(),
      null,
      true
    );

    wp_enqueue_script(
      'jspdf',
      'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
      [],
      null,
      true
    );
  }

  /**
   * Register settings (future use)
   */
  public function register_settings()
  {
    register_setting('wsa_settings_group', 'wsa_api_key');
    register_setting('wsa_settings_group', 'wsa_scan_url');
    register_setting('wsa_settings_group', 'wsa_email');
    register_setting('wsa_settings_group', 'wsa_license_key');
    register_setting('wsa_settings_group', 'wsa_pro_active');
  }
}
