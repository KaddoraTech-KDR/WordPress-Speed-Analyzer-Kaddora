<?php

if (!defined('ABSPATH')) {
  exit;
}

class WSA_Audit_Manager
{

  private $suggestions_engine;
  private $plugin_impact_analyzer;
  private $database_analyzer;
  private $pagespeed_api;

  public function __construct($pagespeed_api = null, $suggestions_engine = null, $plugin_impact_analyzer = null, $database_analyzer = null)
  {
    $this->suggestions_engine = $suggestions_engine;
    $this->plugin_impact_analyzer = $plugin_impact_analyzer;
    $this->database_analyzer = $database_analyzer;
    $this->pagespeed_api = $pagespeed_api;
  }

  private function maybe_send_email($score)
  {
    $email = get_option('wsa_email');

    if (!$email) return;

    // Only send if score is bad
    if ($score > 50) return;

    $subject = '⚠️ Website Performance Alert';

    $message = "Your website performance score is low: " . $score . "\n\n";
    $message .= "Please optimize your website.";

    wp_mail($email, $subject, $message);
  }

  /**
   * Run audit (FAKE DATA)
   */
  public function run()
  {
    $url = get_option('wsa_scan_url', home_url());

    // Toggle fake/real
    $is_fake = true;

    if ($is_fake) {

      // Fake data
      $score = rand(45, 95);
      $lcp = '3.2s';
      $cls = '0.15';
      $fid = '120ms';
      $speed = '3.5s';
    } else {

      // REAL API
      $response = $this->pagespeed_api->analyze($url);

      // Error handling
      if (isset($response['error'])) {
        return [
          'success' => false,
          'message' => $response['error']
        ];
      }

      if (empty($response['lighthouseResult'])) {
        return [
          'success' => false,
          'message' => 'Invalid API response'
        ];
      }

      $lighthouse = $response['lighthouseResult'];
      $audits = $lighthouse['audits'];

      // Extract metrics
      $score = round($lighthouse['categories']['performance']['score'] * 100);

      $lcp = $audits['largest-contentful-paint']['displayValue'] ?? 'N/A';
      $cls = $audits['cumulative-layout-shift']['displayValue'] ?? 'N/A';
      $fid = $audits['max-potential-fid']['displayValue'] ?? 'N/A';
      $speed = $audits['speed-index']['displayValue'] ?? 'N/A';
    }

    // Plugin analyze
    $plugin_data = $this->plugin_impact_analyzer
      ? $this->plugin_impact_analyzer->analyze()
      : [
        'total_plugins' => 0,
        'heavy_plugins' => [],
        'all_plugins' => []
      ];

    // Database analyze
    $db_data = $this->database_analyzer
      ? $this->database_analyzer->analyze()
      : [
        'total_size' => '0 MB',
        'autoload_size' => '0 MB',
        'revisions' => 0
      ];

    // Suggestions
    $suggestions = [];

    if ($this->suggestions_engine) {
      $suggestions = $this->suggestions_engine->generate([
        'score' => $score,
        'lcp'   => $lcp,
        'cls'   => $cls,
        'fid'   => $fid,
        'speed_index' => $speed
      ]);
    }

    // Save report
    $reports = get_option('wsa_reports', []);

    $reports[] = [
      'time' => current_time('mysql'),
      'data' => [
        'score' => $score,
        'lcp' => $lcp,
        'cls' => $cls,
        'fid' => $fid,
        'speed_index' => $speed
      ]
    ];

    if (count($reports) > 10) {
      array_shift($reports);
    }

    update_option('wsa_reports', $reports);

    return [
      'success' => true,
      'data' => [
        'score' => $score,
        'lcp'   => $lcp,
        'cls'   => $cls,
        'fid'   => $fid,
        'speed_index' => $speed,
        'suggestions' => $suggestions,
        'plugins' => $plugin_data,
        'database' => $db_data,
        'reports' => $reports
      ]
    ];
  }
}
