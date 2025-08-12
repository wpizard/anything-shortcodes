<?php
/**
 * Renders the [anys type="user-meta"] shortcode output.
 *
 * Retrieves a user meta value based on the user ID and meta key.
 *
 * Expected attributes:
 * - id: User ID (optional, defaults to current user)
 * - name: User meta key (required)
 * - before: Content to prepend before the output (optional)
 * - after: Content to append after the output (optional)
 * - fallback: Content to display if the value is empty (optional)
 * - format: Formatting for date, datetime, number, etc. (optional)
 */

defined( 'ABSPATH' ) || die();

// Parses dynamic shortcode attributes.
$attributes = anys_parse_dynamic_attributes( $attributes ?? [] );

$key     = $attributes['name'];
$user_id = ! empty( $attributes['id'] ) ? intval( $attributes['id'] ) : get_current_user_id();

// Retrieves the user meta value.
$value = get_user_meta( $user_id, $key, true );

// Applies formatting if specified.
$value = anys_format_value( $value, $attributes );

// Wraps the output with before/after content and fallback.
$output = anys_wrap_output( $value, $attributes );

// Outputs the sanitized content.
echo wp_kses_post( $output );
