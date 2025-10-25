<?php

namespace AnyS;

defined( 'ABSPATH' ) || die();

/**
 * Support Custom Link Class.
 *
 * @since NEXT
 */
final class Nav_Menu {

    /**
     * The instance.
     *
     * @since NEXT
     */
    private static $instance;

    /**
     * Returns the instance.
     *
     * @since NEXT
     *
     * @return Nav_Menu
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
     * @since NEXT
     */
    private function __construct() {
        $this->add_hooks();
    }

    /**
     * Adds hooks.
     *
     * @since NEXT
     */
    protected function add_hooks() {
        add_filter( 'wp_nav_menu_objects', [ $this, 'process_menu_shortcodes' ] );
        add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'admin_menu_item_preview' ], 10, 4 );
    }

    /**
     * Process shortcodes in menu URLs and titles (frontend/admin render).
     *
     * @since NEXT
     *
     * @param array $items Menu items.
     * @return array
     */
    public function process_menu_shortcodes( $items ) {
        foreach ( $items as $item ) {
            if ( ! is_object( $item ) ) {
                continue;
            }

            // URL shortcode
            $url_raw = isset( $item->url ) ? (string) $item->url : '';
            $url_dec = rawurldecode( html_entity_decode( $url_raw, ENT_QUOTES ) );

            if ( preg_match( '#^(?:https?://)?\[[^\]]+\]$#i', $url_dec ) ) {
                if ( preg_match( '#\[(.+)\]#s', $url_dec, $m ) === 1 ) {
                    $shortcode = '[' . trim( $m[1] ) . ']';

                    // Force format="url" for [anys type="link"]
                    if ( preg_match( '/^\[\s*anys\b/i', $shortcode )
                        && preg_match( '/\btype\s*=\s*["\']link["\']/i', $shortcode ) ) {
                        $shortcode = anys_force_shortcode_attr( $shortcode, 'format', 'url' );
                    }

                    $output = do_shortcode( $shortcode );
                    $raw    = trim( wp_strip_all_tags( (string) $output ) );

                    if ( ! empty( $raw ) && filter_var( $raw, FILTER_VALIDATE_URL ) ) {
                        // If the shortcode output is a valid URL, assign it
                        $item->url = esc_url( $raw );
                    }
                }
            }

            // Title shortcode
            $title = isset( $item->title ) ? (string) $item->title : '';

            if ( strpos( $title, '[' ) === 0 && substr( $title, -1 ) === ']' ) {
                if ( preg_match( '/^\[([a-zA-Z0-9_-]+)(?:\s+[^]]*?)?\]$/', $title ) !== 1 ) {
                    continue;
                }

                $output = do_shortcode( $title );
                if ( empty( $output ) ) {
                    $item->title = esc_html( $item->post_title ?? '' );
                    continue;
                }

                $item->title = esc_html( wp_strip_all_tags( (string) $output ) );
            }
        }

        return $items;
    }

    /**
     * Admin preview under menu item fields (does not change saved values).
     *
     * @since NEXT
     *
     * @param int     $item_id
     * @param \WP_Post $item
     * @param int     $depth
     * @param array   $args
     */
    public function admin_menu_item_preview( $item_id, $item, $depth, $args ) {
        if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
            return;
        }
        $screen = get_current_screen();

        if ( ! $screen || $screen->base !== 'nav-menus' ) {
            return;
        }

        // Title preview
        $title_preview = '';
        $title_raw     = (string) $item->title;
        
        if ( strpos( $title_raw, '[' ) === 0 && substr( $title_raw, -1 ) === ']' && preg_match( '/^\[[^\]]+\]$/', $title_raw ) ) {
            $out = do_shortcode( $title_raw );
            if ( $out !== '' && $out !== null ) {
                $title_preview = esc_html( wp_strip_all_tags( (string) $out ) );
            }
        }

        // URL preview: [anys ...] or http(s)://[anys ...]
        $url_preview = '';
        $url_raw     = isset( $item->url ) ? (string) $item->url : '';
        $url_dec     = rawurldecode( html_entity_decode( $url_raw, ENT_QUOTES ) );

        if ( preg_match( '#^(?:https?://)?\[[^\]]+\]$#i', $url_dec ) && preg_match( '#\[(.+)\]#s', $url_dec, $m ) ) {
            $sc = '[' . trim( $m[1] ) . ']';
            if ( preg_match( '/^\[\s*anys\b/i', $sc ) && preg_match( '/\btype\s*=\s*["\']link["\']/i', $sc ) ) {
                $sc = anys_force_shortcode_attr( $sc, 'format', 'url' );
            }
            $out = do_shortcode( $sc );
            $raw = trim( wp_strip_all_tags( (string) $out ) );
            if ( filter_var( $raw, FILTER_VALIDATE_URL ) ) {
                $url_preview = esc_url( $raw );
            }
        }

        if ( $title_preview || $url_preview ) {
            echo '<div class="description-wide" style="margin-top:6px">';
            if ( $title_preview ) {
                echo '<p style="margin:2px 0;"><em>' . esc_html__( 'Title preview:', 'anys' ) . '</em> ' . $title_preview . '</p>';
            }
            if ( $url_preview ) {
                echo '<p style="margin:2px 0;"><em>' . esc_html__( 'URL preview:', 'anys' ) . '</em> ' . $url_preview . '</p>';
            }
            echo '</div>';
        }
    }
}

/** @since NEXT */
Nav_Menu::get_instance();
