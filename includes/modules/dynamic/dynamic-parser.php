<?php

namespace AnyS\Modules\Dynamic;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Parses dynamic placeholders for attribute values.
 *
 * @since NEXT
 */
final class Dynamic_Parser {
    use Singleton;

    /**
     * Stores prefix-to-handler mappings.
     *
     * @var array<string,string>
     */
    private $handlers = [];

    /**
     * Initializes the parser.
     *
     * @since NEXT
     */
    private function __construct() {
        $this->register_handlers();
    }

    /**
     * Registers long and short placeholder handlers.
     *
     * @since NEXT
     *
     * @return void
     */
    private function register_handlers() {
        $this->handlers = [
            // Request based.
            'get'     => 'handle_get',
            'ge'      => 'handle_get',
            'post'    => 'handle_post',
            'po'      => 'handle_post',
            'request' => 'handle_request',
            'rq'      => 'handle_request',

            'server'  => 'handle_server',
            'sv'      => 'handle_server',
            'cookie'  => 'handle_cookie',
            'ck'      => 'handle_cookie',
            'session' => 'handle_session',
            'ss'      => 'handle_session',

            // Options.
            'option'  => 'handle_option',
            'op'      => 'handle_option',

            // Post / term / user context.
            'post_field' => 'handle_post_field',
            'pf'         => 'handle_post_field',
            'post_meta'  => 'handle_post_meta',
            'pm'         => 'handle_post_meta',
            'term_field' => 'handle_term_field',
            'tf'         => 'handle_term_field',
            'term_meta'  => 'handle_term_meta',
            'tm'         => 'handle_term_meta',
            'user_field' => 'handle_user_field',
            'uf'         => 'handle_user_field',
            'user_meta'  => 'handle_user_meta',
            'um'         => 'handle_user_meta',

            // URL.
            'url' => 'handle_url',
            'ur'  => 'handle_url',

            // Functions, shortcodes, constants.
            'func'      => 'handle_func',
            'fn'        => 'handle_func',
            'shortcode' => 'handle_shortcode',
            'sc'        => 'handle_shortcode',
            'const'     => 'handle_const',
            'co'        => 'handle_const',
        ];

        $this->handlers = apply_filters( 'anys/dynamic_parser/handlers', $this->handlers );
    }

    /**
     * Parses a string and resolves placeholders.
     *
     * Arrays and caching are handled in helpers.
     *
     * @since NEXT
     *
     * @param string $value Raw value.
     *
     * @return string
     */
    public function parse( $value ) {
        if ( ! is_string( $value ) ) {
            return $value;
        }

        if ( $value === '' ) {
            return $value;
        }

        return preg_replace_callback(
            '/\{([a-z_]{2,}):(.*?)\}/i',
            [ $this, 'process_placeholder' ],
            $value
        );
    }

    /**
     * Resolves a single placeholder match.
     *
     * @since NEXT
     *
     * @param array $matches Regex result.
     *
     * @return string
     */
    private function process_placeholder( $matches ) {
        $prefix    = strtolower( $matches[1] );
        $parameter = $matches[2];

        if ( ! isset( $this->handlers[ $prefix ] ) ) {
            return $matches[0];
        }

        $handler_method = $this->handlers[ $prefix ];

        if ( ! method_exists( $this, $handler_method ) ) {
            return $matches[0];
        }

        return call_user_func( [ $this, $handler_method ], $parameter );
    }

    /*
     * Handler methods.
     * Each handler returns a string and sanitizes output.
     */

    /**
     * Handles {get:} and {ge:} placeholders.
     *
     * @param string $key_name Query parameter name.
     *
     * @return string
     */
    private function handle_get( $key_name ) {
        $key_name = trim( $key_name );

        if ( $key_name === '' ) {
            return '';
        }

        if ( ! isset( $_GET[ $key_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return '';
        }

        return sanitize_text_field( wp_unslash( $_GET[ $key_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Handles {post:} and {po:} placeholders.
     *
     * @param string $key_name Post parameter name.
     *
     * @return string
     */
    private function handle_post( $key_name ) {
        $key_name = trim( $key_name );

        if ( $key_name === '' ) {
            return '';
        }

        if ( ! isset( $_POST[ $key_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            return '';
        }

        return sanitize_text_field( wp_unslash( $_POST[ $key_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    }

    /**
     * Handles {request:} and {rq:} placeholders.
     *
     * @param string $key_name Request parameter name.
     *
     * @return string
     */
    private function handle_request( $key_name ) {
        $key_name = trim( $key_name );

        if ( $key_name === '' ) {
            return '';
        }

        if ( ! isset( $_REQUEST[ $key_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return '';
        }

        return sanitize_text_field( wp_unslash( $_REQUEST[ $key_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Handles {server:} and {sv:} placeholders.
     *
     * @param string $key_name Server key.
     *
     * @return string
     */
    private function handle_server( $key_name ) {
        $key_name = trim( $key_name );

        if ( $key_name === '' ) {
            return '';
        }

        if ( ! isset( $_SERVER[ $key_name ] ) ) {
            return '';
        }

        return sanitize_text_field( (string) $_SERVER[ $key_name ] );
    }

    /**
     * Handles {cookie:} and {ck:} placeholders.
     *
     * @param string $key_name Cookie name.
     *
     * @return string
     */
    private function handle_cookie( $key_name ) {
        $key_name = trim( $key_name );

        if ( $key_name === '' ) {
            return '';
        }

        if ( ! isset( $_COOKIE[ $key_name ] ) ) { // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cookies_cookie
            return '';
        }

        return sanitize_text_field( wp_unslash( $_COOKIE[ $key_name ] ) ); // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cookies_cookie
    }

    /**
     * Handles {session:} and {ss:} placeholders.
     *
     * @param string $key_name Session key.
     *
     * @return string
     */
    private function handle_session( $key_name ) {
        $key_name = trim( $key_name );

        if ( $key_name === '' ) {
            return '';
        }

        if ( ! isset( $_SESSION ) || ! isset( $_SESSION[ $key_name ] ) ) { // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.session__SESSION
            return '';
        }

        return sanitize_text_field( wp_unslash( $_SESSION[ $key_name ] ) ); // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.session__SESSION
    }

    /**
     * Handles {option:} and {op:} placeholders.
     *
     * @param string $option_name Option name.
     *
     * @return string
     */
    private function handle_option( $option_name ) {
        $option_name = trim( $option_name );

        if ( $option_name === '' ) {
            return '';
        }

        $option_value = get_option( $option_name, '' );

        if ( ! is_string( $option_value ) ) {
            return '';
        }

        return sanitize_text_field( $option_value );
    }

    /**
     * Handles {post_field:} and {pf:} placeholders.
     *
     * @param string $field_name Post field name.
     *
     * @return string
     */
    private function handle_post_field( $field_name ) {
        $field_name = trim( $field_name );
        $post_id    = get_the_ID();

        if ( $post_id === 0 ) {
            return '';
        }

        if ( $field_name === '' ) {
            return '';
        }

        $post_object = get_post( $post_id );

        if ( ! $post_object instanceof \WP_Post ) {
            return '';
        }

        if ( ! isset( $post_object->{$field_name} ) ) {
            return '';
        }

        return sanitize_text_field( (string) $post_object->{$field_name} );
    }

    /**
     * Handles {post_meta:} and {pm:} placeholders.
     *
     * @param string $meta_key Meta key.
     *
     * @return string
     */
    private function handle_post_meta( $meta_key ) {
        $meta_key = trim( $meta_key );
        $post_id  = get_the_ID();

        if ( $post_id === 0 ) {
            return '';
        }

        if ( $meta_key === '' ) {
            return '';
        }

        $meta_value = get_post_meta( $post_id, $meta_key, true );

        if ( ! is_string( $meta_value ) ) {
            return '';
        }

        return sanitize_text_field( $meta_value );
    }

    /**
     * Handles {term_field:} and {tf:} placeholders.
     *
     * @param string $field_name Term field name.
     *
     * @return string
     */
    private function handle_term_field( $field_name ) {
        $field_name    = trim( $field_name );
        $term_object   = get_queried_object();
        $is_valid_term = $term_object instanceof \WP_Term;

        if ( ! $is_valid_term ) {
            return '';
        }

        if ( $field_name === '' ) {
            return '';
        }

        if ( ! isset( $term_object->{$field_name} ) ) {
            return '';
        }

        return sanitize_text_field( (string) $term_object->{$field_name} );
    }

    /**
     * Handles {term_meta:} and {tm:} placeholders.
     *
     * @param string $meta_key Meta key.
     *
     * @return string
     */
    private function handle_term_meta( $meta_key ) {
        $meta_key    = trim( $meta_key );
        $term_object = get_queried_object();
        $is_valid    = $term_object instanceof \WP_Term;

        if ( ! $is_valid ) {
            return '';
        }

        if ( $meta_key === '' ) {
            return '';
        }

        $meta_value = get_term_meta( $term_object->term_id, $meta_key, true );

        if ( ! is_string( $meta_value ) ) {
            return '';
        }

        return sanitize_text_field( $meta_value );
    }

    /**
     * Handles {user_field:} and {uf:} placeholders.
     *
     * @param string $field_name User field name.
     *
     * @return string
     */
    private function handle_user_field( $field_name ) {
        $field_name = trim( $field_name );
        $user_id    = get_current_user_id();

        if ( $user_id === 0 ) {
            return '';
        }

        if ( $field_name === '' ) {
            return '';
        }

        $user_object = get_user_by( 'id', $user_id );

        if ( ! $user_object instanceof \WP_User ) {
            return '';
        }

        if ( ! isset( $user_object->data->{$field_name} ) ) {
            return '';
        }

        return sanitize_text_field( (string) $user_object->data->{$field_name} );
    }

    /**
     * Handles {user_meta:} and {um:} placeholders.
     *
     * @param string $meta_key Meta key.
     *
     * @return string
     */
    private function handle_user_meta( $meta_key ) {
        $meta_key = trim( $meta_key );
        $user_id  = get_current_user_id();

        if ( $user_id === 0 ) {
            return '';
        }

        if ( $meta_key === '' ) {
            return '';
        }

        $meta_value = get_user_meta( $user_id, $meta_key, true );

        if ( ! is_string( $meta_value ) ) {
            return '';
        }

        return sanitize_text_field( $meta_value );
    }

    /**
     * Handles {url:} and {ur:} placeholders.
     *
     * @param string $type Url type.
     *
     * @return string
     */
    private function handle_url( $type ) {
        $type = trim( strtolower( $type ) );

        if ( ! function_exists( 'anys_resolve_url_placeholder' ) ) {
            return '';
        }

        $resolved_url = anys_resolve_url_placeholder( $type );

        if ( $resolved_url === '' ) {
            return '';
        }

        return esc_url_raw( $resolved_url );
    }

    /**
     * Handles {func:} and {fn:} placeholders.
     *
     * @param string $body Function and arguments.
     *
     * @return string
     */
    private function handle_func( $body ) {
        $body = trim( $body );

        if ( $body === '' ) {
            return '';
        }

        $parts         = array_map( 'trim', explode( ',', $body, 2 ) );
        $function_name = $parts[0] ?? '';
        $raw_arguments = $parts[1] ?? '';

        if ( $function_name === '' ) {
            return '';
        }

        $whitelisted_functions = [];

        if ( function_exists( 'anys_get_whitelisted_functions' ) ) {
            $whitelisted_functions = (array) anys_get_whitelisted_functions();
        }

        if ( ! in_array( $function_name, $whitelisted_functions, true ) ) {
            return '';
        }

        if ( ! function_exists( $function_name ) ) {
            return '';
        }

        $arguments = [];

        if ( $raw_arguments !== '' ) {
            $argument_tokens = array_map( 'trim', explode( ',', $raw_arguments ) );

            foreach ( $argument_tokens as $argument_token ) {
                $arguments[] = $this->parse( $argument_token );
            }
        }

        try {
            $result = call_user_func_array( $function_name, $arguments );
        } catch ( \Throwable $exception ) {
            $result = '';
        }

        if ( ! is_string( $result ) ) {
            return '';
        }

        return sanitize_text_field( $result );
    }

    /**
     * Handles {shortcode:} and {sc:} placeholders.
     *
     * @param string $body Shortcode body.
     *
     * @return string
     */
    private function handle_shortcode( $body ) {
        $body = trim( $body );

        if ( preg_match( '/^\((.*)\)$/', $body, $matches ) ) {
            $body = $matches[1];
        }

        $output = do_shortcode( '[' . $body . ']' );

        return wp_strip_all_tags( $output );
    }

    /**
     * Handles {const:} and {co:} placeholders.
     *
     * @param string $constant_name Constant name.
     * 
     * @return string
     */
    private function handle_const( $constant_name ) {
        $constant_name = trim( $constant_name );

        if ( $constant_name === '' ) {
            return '';
        }

        if ( ! defined( $constant_name ) ) {
            return '';
        }

        $constant_value = constant( $constant_name );

        if ( ! is_string( $constant_value ) ) {
            return '';
        }

        return sanitize_text_field( $constant_value );
    }
}
