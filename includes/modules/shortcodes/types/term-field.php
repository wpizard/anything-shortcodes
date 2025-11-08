<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Retrieves a core term field and renders it.
 *
 * Handles the `[anys type="term-field"]` shortcode.
 *
 * @since NEXT
 */
final class Term_Field extends Base {
    use Singleton;

    public function get_type() {
        return 'term-field';
    }

    protected function get_defaults() {
        return [
            'id'       => 0,
            'name'     => '',
            'taxonomy' => '',
            'before'   => '',
            'after'    => '',
            'fallback' => '',
            'format'   => '',
        ];
    }

    /**
     * Renders the shortcode.
     *
     * @since 1.2.0
     * @since NEXT Moved to class-based structure.
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Enclosed content (optional).
     *
     * @return string
     */
    public function render( array $attributes, string $content ) {
        // Parse dynamic attributes
        $attributes = anys_parse_dynamic_attributes( $attributes );
        $attributes = $this->get_attributes( $attributes );

        $term_field_name = $attributes['name'] ?? '';
        $term_id         = (int) ( $attributes['id'] ?? 0 );
        $taxonomy_name   = $attributes['taxonomy'] ?? '';

        // Try to get current queried term if ID not provided
        if ( $term_id <= 0 && is_tax() ) {
            $queried_object = get_queried_object();
            if ( isset( $queried_object->term_id ) ) {
                $term_id       = (int) $queried_object->term_id;
                $taxonomy_name = $queried_object->taxonomy ?? $taxonomy_name;
            }
        }

        if ( $term_field_name === '' || $term_id <= 0 ) {
            return '';
        }

        // Fetch the term
        $term  = get_term( $term_id, $taxonomy_name );
        $value = ( $term && ! is_wp_error( $term ) && isset( $term->$term_field_name ) )
            ? $term->$term_field_name
            : '';

        // Format and wrap
        $value  = anys_format_value( $value, $attributes );
        $output = anys_wrap_output( $value, $attributes );

        return wp_kses_post( (string) $output );
    }
}
