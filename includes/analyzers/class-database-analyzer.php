<?php
if (!defined("ABSPATH")) exit;

class WSA_Database_Analyzer
{
  public function analyze()
  {
    global $wpdb;

    $tables = $wpdb->get_results("SHOW TABLE STATUS");

    $total_size = 0;

    foreach ($tables as $table) {
      $total_size += $table->Data_length + $table->Index_length;
    }

    $total_size_mb = round($total_size / 1024 / 1024, 2);

    $autoload_size = $wpdb->get_var("
      SELECT SUM(LENGTH(option_value))
      FROM {$wpdb->options}
      WHERE autoload = 'yes'
    ");

    $autoload_size_mb = round($autoload_size / 1024 / 1024, 2);

    $revisions = $wpdb->get_var("
      SELECT COUNT(*)
      FROM {$wpdb->posts}
      WHERE post_type = 'revision'
    ");

    return array(
      'total_size' => $total_size_mb . ' MB',
      'autoload_size' => $autoload_size_mb . ' MB',
      'revisions' => $revisions
    );
  }
}
