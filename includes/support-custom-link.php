<?php
/**
 * Support Custom Link Class.
 *
 * Handles custom shortcode processing for navigation menu URLs.
 *
 * @since 1.0.0
 *
 * @package BS5PC
 */

namespace BS5PC;

defined( 'ABSPATH' ) || die();

/**
 * Support Custom Link Class.
 *
 * @since 1.0.0
 */
final class Support_Custom_Link {

    /**
     * The instance.
     *
     * @since 1.0.0
     *
     * @var Support_Custom_Link
     */
    private static $instance;

    /**
     * Returns the instance.
     *
     * @since 1.0.0
     *
     * @return Support_Custom_Link
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
        $this->add_hooks();
    }

    /**
     * Adds hooks.
     *
     * @since 1.0.0
     */
    protected function add_hooks() {
        add_filter( 'wp_nav_menu_objects', [ $this, 'process_menu_shortcodes' ] );
    }

    /**
     * Processes shortcodes in menu item URLs.
     *
     * @since 1.0.0
     *
     * @param array $items Menu items.
     *
     * @return array Modified menu items.
     */
    public function process_menu_shortcodes( $items ) {
        foreach ( $items as $item ) {
            // Check pattern: starts with #shortcode? and ends with #
            if ( strpos( $item->url, '#shortcode?' ) === 0 && substr( $item->url, -1 ) === '#' ) {
                // Extract encoded shortcode (between ? and #)
                $encoded_shortcode = substr( $item->url, 11, -1 );

                // Decode to normal shortcode (e.g., [anys type="link" name="logout"])
                $shortcode = urldecode( $encoded_shortcode );

                // Basic check to ensure it's a valid shortcode (starts with [ and ends with ])
                if ( strpos( $shortcode, '[' ) === 0 && substr( $shortcode, -1 ) === ']' ) {
                    // Execute the shortcode
                    $shortcode_output = do_shortcode( $shortcode );

                    // If output is non-empty and a valid URL, replace the item URL
                    if ( ! empty( $shortcode_output ) && filter_var( $shortcode_output, FILTER_VALIDATE_URL ) ) {
                        $item->url = esc_url( $shortcode_output );
                    } else {
                        // Log for debugging if there's an issue
                        error_log( sprintf(
                            /* translators: %1$s: Shortcode, %2$s: Shortcode output */
                            __( 'Invalid shortcode output in menu: %1$s - Output: %2$s', 'anys' ),
                            $shortcode,
                            $shortcode_output
                        ) );
                    }
                }
            }
        }

        return $items;
    }
}

/**
 * Initializes the Support_Custom_Link class.
 *
 * @since 1.0.0
 */
Support_Custom_Link::get_instance();
