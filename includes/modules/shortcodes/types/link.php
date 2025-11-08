<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Generates URLs or anchor tags for link types.
 *
 * Handles the `[anys type="link"]` shortcode.
 *
 * @since NEXT
 */
final class Link extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type() {
        return 'link';
    }

    /**
     * Returns the default shortcode attributes.
     *
     * @since NEXT
     *
     * @return array
     */
    protected function get_defaults() {
        return [
            'name'             => '',
            'id'               => 0,
            'login_redirect'   => '',
            'logout_redirect'  => '',
            'label'            => '',
            'label_logged_in'  => '',
            'label_logged_out' => '',
            'target'           => '',
            'format'           => 'raw',
            'before'           => '',
            'after'            => '',
            'fallback'         => '',
        ];
    }

    /**
     * Renders the shortcode.
     *
     * @since 1.3.0
     * @since NEXT Moved to class-based structure.
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Enclosed content (optional).
     *
     * @return string
     */
    public function render( array $attributes, string $content = '' ) {
        $attributes = $this->get_attributes( $attributes );

        // Parses dynamic attributes.
        $attributes = anys_parse_dynamic_attributes( $attributes );

        $name   = sanitize_key( $attributes['name'] ?? '' );
        $format = $attributes['format'] ?? 'raw';
        $target = $attributes['target'] ?? '';

        // Define handlers
        $handlers = [
            'logout'   => fn( $atts ) => wp_logout_url( $atts['logout_redirect'] ?? '' ),
            'login'    => fn( $atts ) => wp_login_url( $atts['login_redirect'] ?? '' ),
            'register' => fn() => wp_registration_url(),
            'home'     => fn() => home_url(),
            'admin'    => fn() => admin_url(),
            'profile'  => fn() => admin_url( 'profile.php' ),
            'post'     => fn( $atts ) => empty( $atts['id'] ) ? '' : ( is_wp_error( $p = get_permalink( (int) $atts['id'] ) ) ? '' : $p ),
            'term'     => fn( $atts ) => empty( $atts['id'] ) ? '' : ( is_wp_error( $t = get_term_link( (int) $atts['id'] ) ) ? '' : $t ),
            'siteurl'  => fn() => site_url(),
            'current'  => fn() => home_url( add_query_arg( [] ) ),
            'auth'     => fn( $atts ) => is_user_logged_in()
                ? wp_logout_url( $atts['logout_redirect'] ?? '' )
                : wp_login_url( $atts['login_redirect'] ?? '' ),
        ];

        /**
         * Allows developers to register or modify link handlers for the `[anys type="link"]` shortcode.
         *
         * @since 1.3.0
         *
         * @param array $handlers The list of available link handlers.
         *
         * @return array Modified list of link handlers.
         */
        $handlers = apply_filters( 'anys/link/handlers', $handlers );

        // Resolves URL.
        $url   = isset( $handlers[ $name ] ) ? call_user_func( $handlers[ $name ], $attributes ) : '';
        $label = $attributes['label'] ?: ucfirst( $name );

        // Adjusts label for auth.
        if ( $name === 'auth' ) {
            $label = is_user_logged_in()
                ? ( $attributes['label_logged_in']  ?: esc_html__( 'Logout', 'anys' ) )
                : ( $attributes['label_logged_out'] ?: esc_html__( 'Login', 'anys' ) );
        }

        $value = esc_url( $url );

        // Builds anchor.
        if ( $format === 'anchor' ) {
            $t = $target ? sprintf( ' target="%s"', esc_attr( $target ) ) : '';
            $value = sprintf( '<a href="%s"%s class="anys-link">%s</a>', esc_url( $url ), $t, esc_html( $label ) );
        }

        // Wraps and returns.
        $output = anys_wrap_output( $value, $attributes );

        return wp_kses_post( $output );
    }
}
