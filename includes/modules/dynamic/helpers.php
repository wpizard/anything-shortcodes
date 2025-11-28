<?php

defined( 'ABSPATH' ) || exit;

/**
 * Resolves a URL placeholder type to a concrete URL.
 *
 * @since NEXT
 *
 * @param string $type Placeholder type key.
 * 
 * @return string Resolved URL or empty string.
 */
function anys_resolve_url_placeholder( string $type ): string {
    $normalized_type = trim( strtolower( $type ) );

    if ( $normalized_type === '' ) {
        return '';
    }

    switch ( $normalized_type ) {
        case 'home':
        case 'front':
            return home_url( '/' );

        case 'site':
            return site_url( '/' );

        case 'admin':
            return admin_url();

        case 'login':
            return wp_login_url();

        case 'logout':
            return wp_logout_url();

        case 'register':
            return wp_registration_url();

        case 'lostpassword':
        case 'lost-password':
            return wp_lostpassword_url();

        case 'current':
            $request_uri = isset( $_SERVER['REQUEST_URI'] )
                ? wp_unslash( $_SERVER['REQUEST_URI'] )
                : '';

            if ( $request_uri === '' ) {
                return '';
            }

            return home_url( $request_uri );
    }

    $resolved_url = apply_filters( 'anys_resolve_url_placeholder', '', $normalized_type );

    return is_string( $resolved_url ) ? $resolved_url : '';
}
