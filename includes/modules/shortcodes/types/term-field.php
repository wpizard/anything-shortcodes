<?php
/**
 * Renders the [anys type="term-field"] shortcode output.
 *
 * Expected attributes:
 * - id: Term ID (optional, defaults to current term if in a taxonomy loop)
 * - name: Term field name (required)
 * - taxonomy: Taxonomy name (optional, helps resolve term if needed)
 * - before: Content before the value (optional)
 * - after: Content after the value (optional)
 * - fallback: Fallback content if the value is empty (optional)
 * - format: Formatting for date, number, etc. (optional)
 *
 * @since 1.2.0
 */

defined( 'ABSPATH' ) || die();

// Parses dynamic attributes first with security and caching.
$attributes = anys_parse_dynamic_attributes( $attributes ?? [] );

$key      = $attributes['name'] ?? '';
$term_id  = ! empty( $attributes['id'] ) ? intval( $attributes['id'] ) : null;
$taxonomy = $attributes['taxonomy'] ?? '';

// Get term object (try current term if no id provided).
if ( ! $term_id && is_tax() ) {
    $queried_object = get_queried_object();

    if ( isset( $queried_object->term_id ) ) {
        $term_id  = intval( $queried_object->term_id );
        $taxonomy = $queried_object->taxonomy ?? $taxonomy;
    }
}

$term  = $term_id ? get_term( $term_id, $taxonomy ) : null;
$value = ( $term && isset( $term->$key ) ) ? $term->$key : '';

// Formats the value if needed.
$value = anys_format_value( $value, $attributes );

// Wraps with before/after and apply fallback.
$output = anys_wrap_output( $value, $attributes );

// Outputs the sanitized content.
echo wp_kses_post( $output );
