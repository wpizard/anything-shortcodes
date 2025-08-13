<?php
/**
 * Renders the [anys type="user-field"] shortcode output.
 *
 * Retrieves a standard user field value based on the user ID and field name.
 *
 * Expected attributes:
 * - id: User ID (optional, defaults to current user)
 * - name: User field name (required)
 * - before: Content to prepend before the output (optional)
 * - after: Content to append after the output (optional)
 * - fallback: Content to display if the value is empty (optional)
 * - format: Formatting for date, datetime, number, etc. (optional)
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die();

// Parses dynamic shortcode attributes.
$attributes = anys_parse_dynamic_attributes( $attributes ?? [] );

$key     = $attributes['name'];
$user_id = ! empty( $attributes['id'] ) ? intval( $attributes['id'] ) : get_current_user_id();

// Retrieves user data object.
$user = get_userdata( $user_id );

// Retrieves the user field value or empty string if not set.
$value = ( $user && isset( $user->$key ) ) ? $user->$key : '';

// Applies formatting if specified.
$value = anys_format_value( $value, $attributes );

// Wraps the output with before/after content and fallback.
$output = anys_wrap_output( $value, $attributes );

// Outputs the sanitized content.
echo wp_kses_post( $output );
