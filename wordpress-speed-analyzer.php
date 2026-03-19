<?php

/**
 * Plugin Name: WordPress Speed Analyzer Kaddora
 * Description: Analyze WordPress site speed with PageSpeed Insights, Core Web Vitals, plugin impact checks, and database analysis.
 * Version:     1.0.0
 * Author:      Kaddora Tech
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wordpress-speed-analyzer
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Tested up to: 6.5
 *
 * @package WordPress_Speed_Analyzer
 */

if (! defined('ABSPATH')) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Plugin Constants
|--------------------------------------------------------------------------
*/
define('WSA_VERSION', '1.0.0');
define('WSA_FILE', __FILE__);
define('WSA_PATH', plugin_dir_path(__FILE__));
define('WSA_URL', plugin_dir_url(__FILE__));
define('WSA_BASENAME', plugin_basename(__FILE__));

/*
|--------------------------------------------------------------------------
| Minimum Requirements Check
|--------------------------------------------------------------------------
*/
if (version_compare(PHP_VERSION, '7.4', '<')) {
	add_action(
		'admin_notices',
		static function () {
?>
		<div class="notice notice-error">
			<p>
				<?php esc_html_e('WordPress Speed Analyzer requires PHP 7.4 or higher.', 'wordpress-speed-analyzer'); ?>
			</p>
		</div>
<?php
		}
	);

	return;
}

/*
|--------------------------------------------------------------------------
| Load Required Files
|--------------------------------------------------------------------------
*/
require_once WSA_PATH . 'includes/class-activator.php';
require_once WSA_PATH . 'includes/class-deactivator.php';
require_once WSA_PATH . 'includes/class-helper.php';
require_once WSA_PATH . 'includes/class-plugin.php';

require_once WSA_PATH . 'includes/admin/class-admin.php';
require_once WSA_PATH . 'includes/api/class-pagespeed-api.php';
require_once WSA_PATH . 'includes/audit/class-audit-manager.php';
require_once WSA_PATH . 'includes/audit/class-suggestions-engine.php';
require_once WSA_PATH . 'includes/analyzers/class-plugin-impact-analyzer.php';
require_once WSA_PATH . 'includes/analyzers/class-database-analyzer.php';
require_once WSA_PATH . 'includes/rest/class-rest-controller.php';
require_once WSA_PATH . 'includes/services/class-settings.php';
require_once WSA_PATH . 'includes/services/class-scheduler.php';

/*
|--------------------------------------------------------------------------
| Activation / Deactivation Hooks
|--------------------------------------------------------------------------
*/
register_activation_hook(__FILE__, array('WSA_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('WSA_Deactivator', 'deactivate'));

/*
|--------------------------------------------------------------------------
| Load Text Domain
|--------------------------------------------------------------------------
*/
function wsa_load_textdomain()
{
	load_plugin_textdomain(
		'wordpress-speed-analyzer',
		false,
		dirname(WSA_BASENAME) . '/languages'
	);
}
add_action('plugins_loaded', 'wsa_load_textdomain');

/*
|--------------------------------------------------------------------------
| Initialize Plugin
|--------------------------------------------------------------------------
*/
function wsa_run_plugin()
{
	$plugin = new WSA_Plugin();
	$plugin->run();
}
add_action('plugins_loaded', 'wsa_run_plugin', 20);
