<?php

if (!defined('ABSPATH')) exit;

class WSA_Settings
{

  public function __construct()
  {
    add_action('update_option_wsa_license_key', [$this, 'check_license'], 10, 3);
    add_action('admin_init', [$this, 'manual_check']);
  }

  public function check_license($old_value, $value, $option)
  {
    // error_log("License check triggered: " . $value);

    if ($value === 'PRO123') {
      update_option('wsa_pro_active', true);
    } else {
      update_option('wsa_pro_active', false);
    }
  }

  public function manual_check()
  {
    $license = get_option('wsa_license_key');

    if ($license === 'PRO123') {
      update_option('wsa_pro_active', true);
    } else {
      update_option('wsa_pro_active', false);
    }
  }
}
