<?php
if (!defined("ABSPATH")) exit;

class WSA_Plugin_Impact_Analyzer
{
  public function analyze()
  {
    $plugins = get_option('active_plugins');
    $heavy_plugins = [];

    foreach ($plugins as $plugin) {

      $name = explode('/', $plugin)[0];

      // Basic detection (simple logic)
      if (
        strpos($name, 'elementor') !== false ||
        strpos($name, 'woocommerce') !== false ||
        strpos($name, 'jetpack') !== false
      ) {
        $heavy_plugins[] = $name;
      }
    }

    return array(
      'total_plugins' => count($plugins),
      'heavy_plugins' => $heavy_plugins,
      'all_plugins' => $plugins
    );
  }
}
