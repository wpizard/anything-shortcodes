<?php
/**
 * Plugin Name: Anything Shortcodes
 * Plugin URI: https://wordpress.org/plugins/anything-shortcodes
 * Description: Ready-to-use shortcodes for accessing any data in WordPress.
 * Version: 1.0.0
 * Author: WPizard
 * Author URI: https://wpizard.com/
 * Text Domain: anything-shortcodes
 */

namespace Anything_Shortcodes;

defined( 'ABSPATH' ) or die();

/**
 * RAC class.
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
     * Plugin version.
     *
     * @since 1.0.0
     */
    private static $plugin_version;

    /**
     * Plugin basename.
     *
     * @since 1.0.0
     */
    private static $plugin_basename;

    /**
     * Plugin name.
     *
     * @since 1.0.0
     */
    private static $plugin_name;

    /**
     * Plugin slug.
     *
     * @since 1.0.0
     */
    private static $plugin_slug;

    /**
     * Plugin directory.
     *
     * @since 1.0.0
     */
    private static $plugin_dir;

    /**
     * Plugin url.
     *
     * @since 1.0.0
     */
    private static $plugin_url;

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
        $safe_mode = filter_input( INPUT_GET, 'anything_shortcodes_safe_mode', FILTER_SANITIZE_SPECIAL_CHARS );

        return boolval( $safe_mode );
    }

    /**
     * Defines constants.
     *
     * @since 1.0.0
     */
    protected function define_constants() {
        $plugin_data = get_file_data( __FILE__, [ 'Plugin Name', 'Version' ], 'anything-shortcodes' );

        self::$plugin_basename = plugin_basename( __FILE__ );
        self::$plugin_name     = array_shift( $plugin_data );
        self::$plugin_slug     = strtolower( self::$plugin_name );
        self::$plugin_version  = array_shift( $plugin_data );
        self::$plugin_dir      = trailingslashit( plugin_dir_path( __FILE__ ) );
        self::$plugin_url      = trailingslashit( plugin_dir_url( __FILE__ ) );
    }

    /**
     * Adds hooks.
     *
     * @since 1.0.0
     */
    protected function add_hooks() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    /**
     * Initializes.
     *
     * @since 1.0.0
     */
    public function init() {
        load_plugin_textdomain( 'anything-shortcodes', false, $this->plugin_dir() . '/languages' );

        $this->load_files( [
            'utilities',
            'shortcodes/register',
        ] );

        do_action( 'anything-shortcodes/init', $this );
    }

    /**
     * Gets the plugin version.
     *
     * @since 1.0.0
     */
    public function plugin_version() {
        return self::$plugin_version;
    }

    /**
     * Gets the plugin basename.
     *
     * @since 1.0.0
     */
    public function plugin_basename() {
        return self::$plugin_basename;
    }

    /**
     * Gets the plugin slug.
     *
     * @since 1.0.0
     */
    public function plugin_slug() {
        return self::$plugin_slug;
    }

    /**
     * Gets the plugin name.
     *
     * @since 1.0.0
     */
    public function plugin_name() {
        return self::$plugin_name;
    }

    /**
     * Gets the plugin directory.
     *
     * @since 1.0.0
     */
    public function plugin_dir() {
        return self::$plugin_dir;
    }

    /**
     * Gets the plugin url.
     *
     * @since 1.0.0
     */
    public function plugin_url() {
        return self::$plugin_url;
    }

    /**
     * Loads a directory.
     *
     * @since 1.0.0
     */
    public function load_directory( $directory_name ) {
        $path       = trailingslashit( $this->plugin_dir() . 'includes/' . $directory_name );
        $file_names = glob( $path . '*.php' );

        foreach ( $file_names as $filename ) {
            if ( file_exists( $filename ) ) {
                require_once $filename;
            }
        }
    }

    /**
     * Loads files.
     *
     * @since 1.0.0
     */
    public function load_files( $file_names = [], $base = false, $attributes = [] ) {
        foreach ( $file_names as $file_name ) {
            $this->load_file( $file_name );
        }
    }

    /**
     * Loads a file.
     *
     * @since 1.0.0
     */
    public function load_file( $file_name = '', $base = false, $attributes = [] ) {
        $base       = empty( $base ) ? 'includes/' : '/';
        $attributes = $attributes;

        if ( file_exists( $path = $this->plugin_dir() . $base . $file_name . '.php' ) ) {
            require_once( $path );
        }
    }
}

/**
 * Returns the application instance.
 *
 * @since 1.0.0
 *
 * @return Anything_Shortcodes
 */
function anything_shortcodes() {
    return Plugin::get_instance();
}

/**
 * Initializes the application.
 *
 * @since 1.0.0
 */
anything_shortcodes();
