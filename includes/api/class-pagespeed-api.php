<?php

if (!defined('ABSPATH')) {
  exit;
}

class WSA_PageSpeed_API
{

  public function analyze($url)
  {
    // Cache (VERY IMPORTANT)
    $cache_key = 'wsa_' . md5($url);
    $cached = get_transient($cache_key);

    if ($cached !== false) {
      return $cached;
    }

    // API without key
    $api_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . urlencode($url) . '&strategy=mobile';

    $request = wp_remote_get($api_url, [
      'timeout' => 20,
      'user-agent' => 'Mozilla/5.0'
    ]);

    if (is_wp_error($request)) {
      return ['error' => 'Unable to connect to PageSpeed API'];
    }

    $body = wp_remote_retrieve_body($request);
    $data = json_decode($body, true);

    // Error handle
    if (isset($data['error'])) {

      // Rate limit / quota case
      if ($data['error']['code'] == 429) {
        if (isset($result['error'])) {
          echo '<div class="notice notice-error"><p>' . esc_html($result['error']) . '</p></div>';
        }
      }

      return [
        'error' => $data['error']['message']
      ];
    }

    // Invalid response
    if (empty($data['lighthouseResult'])) {
      return [
        'error' => 'Invalid response from Google API'
      ];
    }

    // Save cache (6 hours)
    set_transient($cache_key, $data, 6 * HOUR_IN_SECONDS);

    return $data;
  }
}
