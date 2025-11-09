<?php

namespace AnyS\Modules;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Nav Menu module.
 *
 * Handles shortcode processing in navigation menus.
 *
 * @since 1.4.0
 */
final class Nav_Menu {
    use Singleton;

    /**
     * Adds hooks.
     *
     * @since 1.4.0
     */
    protected function add_hooks() {
        add_filter( 'wp_nav_menu_objects', [ $this, 'process_menu_shortcodes' ] );

        // Temporarily disabled admin preview — until further decision.
        // add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'admin_menu_item_preview' ], 10, 4 );
    }

    /**
     * Process shortcodes in menu URLs and titles (frontend/admin render).
     *
     * @since 1.4.0
     *
     * @param array $items Menu items.
     *
     * @return array
     */
    public function process_menu_shortcodes( $items ) {
        foreach ( $items as $item ) {
            if ( ! is_object( $item ) ) {
                continue;
            }

            // Processes shortcodes in URL.
            $url_raw = isset( $item->url ) ? (string) $item->url : '';
            $url_decoded = rawurldecode( html_entity_decode( $url_raw, ENT_QUOTES ) );

            if ( anys_has_shortcode( $url_decoded ) ) {
                $item->url = $this->anys_resolve_menu_input($url_decoded, '');
            }

            // Processes shortcodes in title.
            if ( isset( $item->title ) && anys_has_shortcode( $item->title ) ) {
                $output = do_shortcode( $item->title );

                if ( ! empty( $output ) && $output !== $item->title ) {
                    // Updates title with shortcode output.
                    $item->title = esc_html( wp_strip_all_tags( (string) $output ) );
                }
            }
        }

        return $items;
    }

    /**
     * Admin preview under menu item fields (does not change saved values).
     *
     * @since 1.4.0
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

        // Title preview.
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

    /**
     * Normalizes unquoted shortcode attributes into a quoted form.
     *
     * Preserves internal spaces/commas and escapes internal double quotes.
     *
     * @since NEXT
     *
     * @param string $shortcode Shortcode input.
     *
     * @return string Quoted shortcode or original input on failure.
     */
    private function anys_quote_shortcode_attributes( string $shortcode ): string {
        if ( ! preg_match( '/^\s*\[([A-Za-z0-9_\-]+)\s*(.*?)\]\s*$/s', $shortcode, $m ) ) {
            return $shortcode;
        }

        $tag  = $m[1];
        $body = $m[2];
        $out  = '[' . $tag;

        // Matches key=value pairs until the next key or closing bracket.
        if ( preg_match_all(
            '/([A-Za-z0-9_\-]+)=((?:(?!\s+[A-Za-z0-9_\-]+=).)*)/s',
            $body,
            $pairs,
            PREG_SET_ORDER
        ) ) {
            foreach ( $pairs as $p ) {
                $key = $p[1];
                $val = rtrim( $p[2] ); // trims only trailing spaces at the end of value.
                $val = str_replace( '"', '&quot;', $val );
                $out .= ' ' . $key . '="' . $val . '"';
            }
            $out .= ']';
            return $out;
        }
        return $shortcode;
    }

    /**
     * Resolves a menu input that may embed a shortcode, optionally prefixed by a scheme.
     *
     * @since NEXT
     *
     * @param string $raw      Raw menu input.
     * @param string $fallback Fallback URL on failure.
     *
     * @return string Resolved absolute URL or fallback.
     */
    private function anys_resolve_menu_input( string $raw, string $fallback = '' ): string {
        $candidate = trim( $raw );

        // Extracts optional http(s) scheme before the shortcode.
        $forced_scheme = null;
        if ( preg_match( '#^\s*(https?://)\s*(%5B|\[)#i', $candidate, $m ) ) {
            $forced_scheme = stripos( $m[1], 'https://' ) === 0 ? 'https' : 'http';
            // Strips the leading scheme to isolate the shortcode block.
            $candidate = preg_replace( '#^\s*https?://\s*#i', '', $candidate, 1 );
        }

        // Decodes percent-encoded input.
        if ( strpos( $candidate, '%' ) !== false ) {
            $candidate = rawurldecode( $candidate );
        }

        // Handles an embedded shortcode.
        if ( isset( $candidate[0] ) && $candidate[0] === '[' ) {
            $normalized = $this->anys_quote_shortcode_attributes( $candidate );
            $rendered   = do_shortcode( $normalized );
            $value      = trim( wp_strip_all_tags( (string) $rendered ) );

            if ( $value === '' ) {
                return $fallback;
            }

            // Blocks unsafe schemes (security hardening).
            if ( preg_match( '#^(?:javascript:|data:)#i', $value ) ) {
                return $fallback;
            }

            // Allows non-HTTP schemes.
            if ( preg_match( '#^(?:mailto:|tel:)#i', $value ) ) {
                return $value;
            }

            // Handles protocol-relative URLs.
            if ( strpos( $value, '//' ) === 0 ) {
                return $forced_scheme ? $forced_scheme . ':' . $value : set_url_scheme( $value, 'https' );
            }

            // Normalizes scheme for absolute URLs when a scheme is forced.
            if ( wp_http_validate_url( $value ) ) {
                return $forced_scheme ? set_url_scheme( $value, $forced_scheme ) : $value;
            }

            // Builds an absolute URL from a relative path.
            $path = ltrim( $value, '/' ); // Keeps query/fragment intact.
            $base = $forced_scheme ? home_url( '/', $forced_scheme ) : home_url( '/' );
            return rtrim( $base, '/' ) . '/' . $path;
        }

        // Normalizes plain URLs with the forced scheme when present.
        if ( wp_http_validate_url( $candidate ) ) {
            return $forced_scheme ? set_url_scheme( $candidate, $forced_scheme ) : $candidate;
        }

        return $fallback;
    }
}

/**
 * Initializes the Nav_Menu class.
 *
 * @since 1.4.0
 */
Nav_Menu::get_instance();
