<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Executes a whitelisted PHP function with optional dynamic arguments.
 *
 * Handles the `[anys type="function"]` shortcode.
 *
 * @since NEXT
 */
final class Function_Type extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type() {
        return 'function';
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
            'name'     => '',
            'before'   => '',
            'after'    => '',
            'fallback' => '',
            'format'   => '',
        ];
    }

    /**
     * Renders the shortcode.
     *
     * @since 1.1.0
     * @since NEXT Moved to class-based structure.
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Enclosed content (optional).
     *
     * @return string
     */
    public function render( array $attributes, string $content ) {
        $attributes = $this->get_attributes( $attributes );

        // Parses dynamic attributes.
        $attributes = anys_parse_dynamic_attributes( $attributes );

        // Extracts function and arguments.
        $parts    = array_map( 'trim', explode( ',', $attributes['name'] ?? '', 2 ) );
        $function = $parts[0] ?? '';
        $args_raw = $parts[1] ?? '';

        if ( $function === '' ) {
            return '';
        }

        // Validates function existence
        if ( ! function_exists( $function ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                return sprintf(
                    /* translators: %s is the function name */
                    esc_html__( 'Function "%s" does not exist.', 'anys' ),
                    esc_html( $function )
                );
            }
            return '';
        }

        // Validates whitelist.
        $whitelisted = (array) anys_get_whitelisted_functions();
        if ( ! in_array( $function, $whitelisted, true ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                $settings_url = admin_url( 'options-general.php?page=anys-settings' );
                return sprintf(
                    /* translators: 1: function name, 2: settings url */
                    esc_html__( 'Function "%1$s" is not whitelisted. Please %2$s.', 'anys' ),
                    esc_html( $function ),
                    sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( $settings_url ),
                        esc_html__( 'add it to whitelisted Functions in settings', 'anys' )
                    )
                );
            }
            return '';
        }

        // Resolves arguments.
        $tokens = $args_raw !== '' ? array_map( 'trim', explode( '|', $args_raw ) ) : [];
        $cache  = [];
        $args   = array_map(
            static function ( $t ) use ( &$cache ) {
                return anys_parse_dynamic_value( $t, $cache );
            },
            $tokens
        );

        // Removes empty-string args.
        $args = array_values(
            array_filter(
                $args,
                static function ( $a ) { return $a !== ''; }
            )
        );

        // Executes target function.
        $value = call_user_func_array( $function, $args );

        // Formats and wraps.
        $value  = anys_format_value( $value, $attributes );
        $output = anys_wrap_output( $value, $attributes );

        // Returns sanitized output.
        return wp_kses_post( (string) $output );
    }
}
