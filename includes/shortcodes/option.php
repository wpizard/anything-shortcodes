<?php
/**
 * Renders the [anys type="option"] shortcode output.
 *
 * Retrieves a WordPress option value based on the option name.
 *
 * Expected attributes:
 * - name: Option name (required)
 * - before: Content to prepend before the output (optional)
 * - after: Content to append after the output (optional)
 * - fallback: Content to display if the value is empty (optional)
 * - format: Formatting for date, datetime, number, etc. (optional)
 */

defined( 'ABSPATH' ) || die();

// Parses dynamic shortcode attributes.
$attributes = anys_parse_dynamic_attributes( $attributes ?? [] );

$key = $attributes['name'];

// Retrieves the option value.
$value = get_option( $key, '' );

// Applies formatting if specified.
$value = anys_format_value( $value, $attributes );

// Wraps the output with before/after content and fallback.
$output = anys_wrap_output( $value, $attributes );

// Outputs the sanitized content.
echo wp_kses_post( $output );
