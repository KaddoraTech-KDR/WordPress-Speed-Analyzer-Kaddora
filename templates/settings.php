<div class="wrap ">
  <h1>🚀 Speed Analyzer Settings</h1>

  <form method="post" action="options.php">
    <?php settings_fields('wsa_settings_group'); ?>

    <table class="form-table">

      <tr>
        <th>API Key</th>
        <td>
          <input type="text" name="wsa_api_key"
            value="<?php echo esc_attr(get_option('wsa_api_key')); ?>"
            class="regular-text">
        </td>
      </tr>

      <tr>
        <th>Scan URL</th>
        <td>
          <input type="text" name="wsa_scan_url"
            value="<?php echo esc_attr(get_option('wsa_scan_url', home_url())); ?>"
            class="regular-text">
        </td>
      </tr>

      <tr>
        <th>Email</th>
        <td>
          <input type="email" name="wsa_email"
            value="<?php echo esc_attr(get_option('wsa_email')); ?>"
            class="regular-text">
        </td>
      </tr>

      <!-- LICENSE KEY -->
      <tr>
        <th>License Key</th>
        <td>
          <input type="text" name="wsa_license_key"
            value="<?php echo esc_attr(get_option('wsa_license_key')); ?>"
            class="regular-text">
        </td>
      </tr>

    </table>

    <?php submit_button(); ?>
  </form>

  <!-- STATUS -->
  <div class="wsa-pro-box">
    <h2>PRO Status</h2>

    <?php if (get_option('wsa_pro_active')): ?>
      <p style="color:green;">✅ PRO Activated</p>
    <?php else: ?>
      <p style="color:red;">❌ Free Version</p>
      <p>Upgrade to unlock Export, Reports & Advanced Features</p>
    <?php endif; ?>

  </div>

</div>