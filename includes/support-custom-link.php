<?php
/**
 * Support Custom Link Class.
 *
 * Handles custom shortcode processing for navigation menu URLs and Link Text.
 *
 * @since 1.0.0
 *
 * @package BS5PC
 */

namespace AnyS;

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
     * Processes shortcodes in menu item URLs and Link Text.
     *
     * @since 1.0.0
     *
     * @param array $items Menu items.
     *
     * @return array Modified menu items.
     */
    public function process_menu_shortcodes( $items ) {
        foreach ( $items as $item ) {
            // Process URL shortcodes (unchanged)
            if ( strpos( $item->url, '#shortcode?' ) === 0 && substr( $item->url, -1 ) === '#' ) {
                $encoded_shortcode = substr( $item->url, 11, -1 );
                $shortcode = urldecode( $encoded_shortcode );

                // Basic check to ensure it's a valid shortcode
                if ( strpos( $shortcode, '[' ) === 0 && substr( $shortcode, -1 ) === ']' ) {
                    $shortcode_output = do_shortcode( $shortcode );

                    if ( ! empty( $shortcode_output ) && filter_var( $shortcode_output, FILTER_VALIDATE_URL ) ) {
                        $item->url = esc_url( $shortcode_output );
                    } else {
                        error_log( sprintf(
                            /* translators: %1$s: Shortcode, %2$s: Shortcode output */
                            __( 'Invalid shortcode output in menu: %1$s - Output: %2$s', 'anys' ),
                            $shortcode,
                            $shortcode_output
                        ) );
                    }
                }
            }

            // Process Link Text shortcodes
            if ( strpos( $item->title, '[' ) === 0 && substr( $item->title, -1 ) === ']' ) {
                $shortcode = $item->title;

                // Validate shortcode format
                if ( preg_match( '/^\[([a-zA-Z0-9_-]+)(?:\s+[^]]*?)?\]$/', $shortcode ) ) {
                    $shortcode_output = do_shortcode( $shortcode );

                    if ( ! empty( $shortcode_output ) ) {
                        $item->title = esc_html( $shortcode_output );
                    } else {
                        // Fallback to post_title or empty string
                        $item->title = esc_html( $item->post_title ?? '' );
                        error_log( sprintf(
                            /* translators: %1$s: Shortcode, %2$s: Shortcode output */
                            __( 'Invalid shortcode output in menu Link Text: %1$s - Output: %2$s', 'anys' ),
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
