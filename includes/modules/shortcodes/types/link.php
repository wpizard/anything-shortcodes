<?php
/**
 * Renders the [anys type="link"] shortcode output.
 *
 * Expected attributes:
 * - name: The link type (required). Examples: logout, login, register, post, term, home, admin, profile, dashboard.
 * - id: Optional ID (post, term, etc. depending on type).
 * - login_redirect: Optional redirect URL for login.
 * - logout_redirect: Optional redirect URL for logout.
 * - label: Optional link text (default varies by type).
 * - label_logged_in: Text to display when user is logged in (default "Logout") for auth type.
 * - label_logged_out: Text to display when user is logged out (default "Login") for auth type.
 * - target: Optional anchor target (e.g., _blank, _self).
 * - format: Output format (optional). Options:
 *     - raw: returns only URL (default)
 *     - anchor: returns clickable <a> tag
 * - before: Content before the value (optional)
 * - after: Content after the value (optional)
 * - fallback: Fallback content if link not found (optional)
 *
 * @since 1.3.0
 */

defined( 'ABSPATH' ) || die();

// Parses dynamic attributes first.
$attributes = anys_parse_dynamic_attributes( $attributes ?? [] );

$name     = sanitize_key( $attributes['name'] ?? '' );
$id       = intval( $attributes['id'] ?? 0 );
$redirect = esc_url_raw( $attributes['redirect'] ?? '' );
$format   = $attributes['format'] ?? 'raw';
$label    = $attributes['label'] ?? ucfirst( $name );
$target   = $attributes['target'] ?? '';

// Defines available link generators.
$handlers = [
    'logout' => fn( $atts ) => wp_logout_url( $atts['logout_redirect'] ?? '' ),
    'login' => fn( $atts ) => wp_login_url( $atts['login_redirect'] ?? '' ),
    'register' => fn() => wp_registration_url(),
    'home' => fn() => home_url(),
    'admin' => fn() => admin_url(),
    'profile' => fn() => admin_url( 'profile.php' ),
    'post' => function( $atts ) {
        if ( empty( $atts['id'] ) ) {
            return '';
        }

        $permalink = get_permalink( intval( $atts['id'] ) );
        return is_wp_error( $permalink ) ? '' : $permalink;
    },
    'term' => function( $atts ) {
        if ( empty( $atts['id'] ) ) {
            return '';
        }

        $term_link = get_term_link( intval( $atts['id'] ) );
        return is_wp_error( $term_link ) ? '' : $term_link;
    },
    'siteurl' => fn() => site_url(),
    'current' => fn() => home_url( add_query_arg( [] ) ),
    'auth' => fn( $atts ) => is_user_logged_in()
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

// Gets the URL for the specified link type.
$url = isset( $handlers[ $name ] ) ? call_user_func( $handlers[ $name ], $attributes ) : '';

// Determines the default label.
$label = $attributes['label'] ?? ucfirst( $name );

// Escapes the URL for safe output.
$value = esc_url( $url );

// Overrides the label dynamically for the auth link type.
if ( $name === 'auth' ) {
    $label = is_user_logged_in()
        ? ( $attributes['label_logged_in'] ?? esc_html__( 'Logout', 'anys' ) )
        : ( $attributes['label_logged_out'] ?? esc_html__( 'Login', 'anys' ) );
}

// Builds the final output as an anchor element if format is set to "anchor".
if ( $format === 'anchor' ) {
    $target_attr = $target ? sprintf( ' target="%s"', esc_attr( $target ) ) : '';
    $value = sprintf( '<a href="%s"%s class="anys-link">%s</a>', esc_url( $url ), $target_attr, esc_html( $label ) );
}

// Wraps with before/after and fallback.
$output = anys_wrap_output( $value, $attributes );

// Outputs the sanitized content.
echo wp_kses_post( $output );
