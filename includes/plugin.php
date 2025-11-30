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

namespace AnyS;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Main Plugin class.
 *
 * Handles initialization, constants, and module loading.
 *
 * @since 1.0.0
 */
final class Plugin {
    use Singleton;

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
    private function safe_mode(): bool {
        $safe_mode = filter_input( INPUT_GET, 'anys_safe_mode', FILTER_SANITIZE_SPECIAL_CHARS );

        return (bool) $safe_mode;
    }

    /**
     * Defines constants.
     *
     * @since 1.0.0
     * @since 1.1.0 Changes Shortcode constant to Types.
     */
    protected function define_constants(): void {
        define( 'ANYS_NAME', 'Anything Shortcodes' );
        define( 'ANYS_SLUG', 'anys' );
        define( 'ANYS_VERSION', '1.4.0' );

        define( 'ANYS_PATH', wp_normalize_path( trailingslashit( plugin_dir_path( __DIR__ ) ) ) );
        define( 'ANYS_INCLUDES_PATH', ANYS_PATH . 'includes/' );
        define( 'ANYS_MODULES_PATH', ANYS_PATH . 'includes/modules/' );
        define( 'ANYS_ASSETS_PATH', ANYS_PATH . 'assets/' );

        define( 'ANYS_URL', wp_normalize_path( trailingslashit( plugin_dir_url( __DIR__ ) ) ) );
        define( 'ANYS_INCLUDES_URL', ANYS_URL . 'includes/' );
        define( 'ANYS_MODULES_URL', ANYS_URL . 'includes/modules/' );
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
    protected function add_hooks(): void {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'anys/init', [ $this, 'load_modules' ] );
    }

    /**
     * Initializes.
     *
     * @since 1.0.0
     */
    public function init(): void {
        /**
         * Loads Anything Shortcodes .
         *
         * @since 1.0.0
         */
        do_action( 'anys/init' );
    }

    /**
     * Loads textdomain.
     *
     * @since 1.0.0
     */
    public function load_textdomain(): void {
        load_plugin_textdomain( 'anys', false, ANYS_PATH . '/languages' );
    }

    /**
     * Loads a file if it exists.
     *
     * @since 1.4.0
     *
     * @param string $file The full path to the file.
     *
     * @return void
     */
    protected function load_file( string $file ): void {
        if (
            ! empty( $file )
            && file_exists( $file )
        ) {
            require_once $file;
        }
    }

    /**
     * Loads modules.
     *
     * @since 1.0.0
     * @since 1.1.0 Changes file name.
     * @since 1.4.0 Dynamic includes & Composer support.
     */
    public function load_modules(): void {
        // Loads Composer autoload if available.
        $this->load_file( ANYS_PATH . '/vendor/autoload.php' );

        // Loads includes dynamically.
        $modules = [
            'utilities.php',
            'cache/cache.php',
            'query/query.php',
            'settings-page/settings-page.php',
            'shortcodes/shortcodes.php',
            'nav-menu/nav-menu.php',
            'elementor/elementor.php',
            'assets/assets.php',
        ];

        foreach ( $modules as $module ) {
            $this->load_file( ANYS_MODULES_PATH . $module );
        }
    }
}
