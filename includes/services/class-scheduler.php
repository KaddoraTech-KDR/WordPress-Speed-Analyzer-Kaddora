<?php
if (!defined("ABSPATH")) exit;

class WSA_Scheduler
{
  /**
   * Register custom schedules
   */
  public function register_schedules()
  {
    add_filter('cron_schedules', function ($schedules) {

      if (!is_array($schedules)) {
        $schedules = [];
      }

      $schedules['wsa_5min'] = [
        'interval' => 300,
        'display'  => 'Every 5 Minutes'
      ];

      return $schedules;
    });
  }

  /**
   * Schedule event (safe)
   */
  public function schedule_event()
  {
    if (!wp_next_scheduled('wsa_run_scheduled_audit')) {
      wp_schedule_event(time(), 'wsa_5min', 'wsa_run_scheduled_audit');
    }
  }

  /**
   * Force reschedule (important)
   */
  public function reschedule_event()
  {
    wp_clear_scheduled_hook('wsa_run_scheduled_audit');
    wp_schedule_event(time(), 'wsa_5min', 'wsa_run_scheduled_audit');
  }

  /**
   * Clear event
   */
  public function clear_event()
  {
    wp_clear_scheduled_hook('wsa_run_scheduled_audit');
  }
}
