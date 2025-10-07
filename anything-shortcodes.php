<?php
/**
 * Plugin Name: Anything Shortcodes
 * Plugin URI: https://wordpress.org/plugins/anything-shortcodes
 * Description: Get and display anything in WordPress with shortcodes.
 * Version: 1.3.0
 * Author: WPizard
 * Author URI: https://wpizard.com/
 * Text Domain: anys
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace AnyS;

defined( 'ABSPATH' ) or die();

/**
 * Anything Shortcodes class.
 *
 * @since 1.0.0
 */
final class Plugin {

    /**
     * The instance.
     *
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Returns the instance.
     *
     * @since 1.0.0
     *
     * @return Plugin
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        if ( $this->safe_mode() ) {
            return;
        }

        $this->define_constants();
        $this->add_hooks();
    }

    /**
     * Adds safe mode.
     *
     * @since 1.0.0
     */
    private function safe_mode() {
        $safe_mode = filter_input( INPUT_GET, 'anys_safe_mode', FILTER_SANITIZE_SPECIAL_CHARS );

        return boolval( $safe_mode );
    }

    /**
     * Defines constants.
     *
     * @since 1.0.0
     * @since 1.1.0 Changes Shortcode constant to Types.
     */
    protected function define_constants() {
        define( 'ANYS_NAME', esc_html__( 'Anything Shortcodes', 'anys' ) );
        define( 'ANYS_SLUG', 'anys' );
        define( 'ANYS_VERSION', '1.3.0' );

        define( 'ANYS_PATH', wp_normalize_path( trailingslashit( plugin_dir_path( __FILE__ ) ) ) );
        define( 'ANYS_INCLUDES_PATH', ANYS_PATH . 'includes/' );
        define( 'ANYS_TYPES_PATH', ANYS_PATH . 'includes/types/' );
        define( 'ANYS_ASSETS_PATH', ANYS_PATH . 'assets/' );

        define( 'ANYS_URL', wp_normalize_path( trailingslashit( plugin_dir_url( __FILE__ ) ) ) );
        define( 'ANYS_INCLUDES_URL', ANYS_URL . 'includes/' );
        define( 'ANYS_TYPES_URL', ANYS_URL . 'includes/types/' );
        define( 'ANYS_ASSETS_URL', ANYS_URL . 'assets/' );
        define( 'ANYS_CSS_URL', ANYS_ASSETS_URL . 'css/' );
        define( 'ANYS_JS_URL', ANYS_ASSETS_URL . 'js/' );
        define( 'ANYS_IMAGES_URL', ANYS_ASSETS_URL . 'images/' );
    }

    /**
     * Adds hooks.
     *
     * @since 1.0.0
     */
    protected function add_hooks() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'anys/init', [ $this, 'load_dependencies' ] );
    }

    /**
     * Initializes.
     *
     * @since 1.0.0
     */
    public function init() {

        /**
         * Fires before Beans loads.
         *
         * @since 1.0.0
         */
        do_action( 'anys/init/before' );

        /**
         * Loads Anything Shortcodes .
         *
         * @since 1.0.0
         */
        do_action( 'anys/init' );

        /**
        * Fires after Beans loads.
        *
        * @since 1.0.0
        */
        do_action( 'anys/init/after' );
    }

    /**
     * Loads textdomain.
     *
     * @since 1.0.0
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'anys', false, ANYS_PATH . '/languages' );
    }

    /**
     * Loads dependencies.
     *
     * @since 1.0.0
     * @since 1.1.0 Changes file name.
     */
    public function load_dependencies() {
        require_once ANYS_INCLUDES_PATH . 'utilities.php';
        require_once ANYS_INCLUDES_PATH . 'settings-page.php';
        require_once ANYS_INCLUDES_PATH . 'register-shortcodes.php';
    }
}

/**
 * Initializes the application.
 *
 * @since 1.0.0
 */
Plugin::get_instance();
