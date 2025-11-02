<?php
/**
 * Renders the [anys type="function"] shortcode output.
 *
 * Executes a safe, whitelisted PHP function with optional arguments.
 *
 * Expected attributes:
 * - name: Function name followed by optional arguments, separated by commas (required)
 *         Example: "date_i18n, Y", "date_i18n, F j, Y|1730505600"
 * - before: Content to prepend before the output (optional)
 * - after: Content to append after the output (optional)
 * - fallback: Content to display if the value is empty (optional)
 * - format: Additional formatting for the returned value (optional)
 *
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || die();

// Parses dynamic shortcode attributes.
$attributes = anys_parse_dynamic_attributes( $attributes ?? [] );

// Splits the function name and arguments from the 'name' attribute.
$parts = array_map( 'trim', explode( ',', $attributes['name'] ?? '', 2 ) );

// Retrieves the function name.
$function = array_shift( $parts );

// Remaining string contains all arguments.
$parts_string = $parts[0] ?? '';

// Splits parts by pipe.
$parts = array_map( 'trim', explode( '|', $parts_string ) );

// Defines the whitelist of whitelisted functions for security.
$whitelisted_functions = anys_get_whitelisted_functions();

// Initializes the output value.
$value = '';

// Validates the function name.
if ( ! $function ) {
    return '';
}

// Checks if the function exists.
if ( ! function_exists( $function ) ) {
    if ( current_user_can( 'manage_options' ) ) {
        echo sprintf(
            /* translators: %s is the function name */
            esc_html__( 'Function "%s" does not exist.', 'anys' ),
            esc_html( $function )
        );
    }

    return '';
}

// Checks if the function is whitelisted.
if ( ! in_array( $function, $whitelisted_functions, true ) ) {
    if ( current_user_can( 'manage_options' ) ) {
        $settings_url = admin_url( 'options-general.php?page=anys-settings' );
        printf(
            /* translators: %1$s is the function name, %2$s is the settings page URL */
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

// Parses each argument dynamically.
$args = array_map( function( $arg ) {
    return anys_parse_dynamic_value( $arg, $cache );
}, $parts );

// Removes empty strings (optional) to avoid passing to functions that take 0 args.
$args = array_filter( $args, function( $arg ) {
    return $arg !== '';
} );

// Calls the whitelisted function with the provided arguments.
$value = call_user_func_array( $function, $args );

// Applies formatting if specified.
$value = anys_format_value( $value, $attributes );

// Wraps the output with before/after content and fallback.
$output = anys_wrap_output( $value, $attributes );

// Outputs the sanitized content.
echo wp_kses_post( $output );
