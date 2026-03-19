<?php

if (!defined('ABSPATH')) {
  exit;
}

class WSA_REST_Controller
{

  private $audit_manager;
  private $settings;

  public function __construct($audit_manager, $settings)
  {
    $this->audit_manager = $audit_manager;
    $this->settings = $settings;
  }

  /**
   * Register REST routes
   */
  public function register_routes()
  {
    register_rest_route('wsa/v1', '/run-audit', [

      'methods'  => 'POST',

      'callback' => [$this, 'run_audit'],

      'permission_callback' => function () {
        return current_user_can('manage_options');
      }

    ]);
  }

  /**
   * Run audit
   */
  public function run_audit()
  {
    return $this->audit_manager->run();
  }
}
