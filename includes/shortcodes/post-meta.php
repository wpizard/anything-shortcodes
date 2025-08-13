<?php
/**
 * Renders the [anys type="post-meta"] shortcode output.
 *
 * Expected attributes:
 * - id: Post ID (optional, defaults to current post)
 * - name: Meta key (required)
 * - before: Content before the value (optional)
 * - after: Content after the value (optional)
 * - fallback: Fallback content if the value is empty (optional)
 * - format: Formatting for date, datetime, number, etc. (optional)
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die();

// Parses dynamic attributes with security and caching.
$attributes = anys_parse_dynamic_attributes( $attributes ?? [] );

$key     = $attributes['name'];
$post_id = ! empty( $attributes['id'] ) ? intval( $attributes['id'] ) : get_the_ID();

// Direct retrieval using WordPress core.
$value = get_post_meta( $post_id, $key, true );

// Formats if requested.
$value = anys_format_value( $value, $attributes );

// Wraps output with before/after and fallback.
$output = anys_wrap_output( $value, $attributes );

// Outputs content safely with allowed HTML.
echo wp_kses_post( $output );
