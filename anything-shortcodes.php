<?php
/**
 * Plugin Name: Anything Shortcodes
 * Plugin URI: https://wordpress.org/plugins/anything-shortcodes
 * Description: Get and display anything in WordPress with shortcodes.
 * Version: 1.4.0
 * Author: WPizard
 * Author URI: https://wpizard.com/
 * Text Domain: anys
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/traits/trait-singleton.php';
require_once __DIR__ . '/includes/plugin.php';

AnyS\Plugin::get_instance();
