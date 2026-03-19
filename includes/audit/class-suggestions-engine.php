<?php

if (!defined('ABSPATH')) {
  exit;
}

class WSA_Suggestions_Engine
{

  public function generate($metrics)
  {
    $suggestions = [];

    // Performance Score
    if ($metrics['score'] < 50) {
      $suggestions[] = "Your site performance is very low. Consider using caching and CDN.";
    }

    // LCP
    if ($this->to_seconds($metrics['lcp']) > 2.5) {
      $suggestions[] = "Optimize images and reduce server response time (LCP is high).";
    }

    // CLS
    if ((float)$metrics['cls'] > 0.1) {
      $suggestions[] = "Reduce layout shifts by setting proper width/height for images (CLS issue).";
    }

    // FID
    if ($this->to_ms($metrics['fid']) > 100) {
      $suggestions[] = "Reduce JavaScript execution time to improve interactivity (FID high).";
    }

    // Speed Index
    if ($this->to_seconds($metrics['speed_index']) > 3) {
      $suggestions[] = "Improve page loading speed by optimizing CSS and JS files.";
    }

    return $suggestions;
  }

  private function to_seconds($value)
  {
    if (!$value) return 0;
    return (float) str_replace('s', '', $value);
  }

  private function to_ms($value)
  {
    if (!$value) return 0;
    return (float) str_replace('ms', '', $value);
  }
}
