<?php
/**
 * Renders the [anys type="post-field"] shortcode output.
 *
 * Expected attributes:
 * - id: Post ID (optional, defaults to current post)
 * - name: Post field name (required)
 * - before: Content before the value (optional)
 * - after: Content after the value (optional)
 * - fallback: Fallback content if the value is empty (optional)
 * - format: Formatting for date, datetime, number, etc. (optional)
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die();

// Parses dynamic attributes first with security and caching.
$attributes = anys_parse_dynamic_attributes( $attributes ?? [] );

$key     = $attributes['name'];
$post_id = ! empty( $attributes['id'] ) ? intval( $attributes['id'] ) : get_the_ID();

$post  = get_post( $post_id );
$value = ( $post && isset( $post->$key ) ) ? $post->$key : '';

// Formats the value if needed.
$value = anys_format_value( $value, $attributes );

// Wraps with before/after and apply fallback.
$output = anys_wrap_output( $value, $attributes );

// Outputs the sanitized content.
echo wp_kses_post( $output );
