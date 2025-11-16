<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Term Meta shortcode type.
 *
 * Retrieves term meta and renders it.
 *
 * Handles the `[anys type="term-meta"]` shortcode.
 *
 * Examples:
 * [anys type="term-meta" id="123" key="color"]
 * [anys type="term-meta" key="seo_title"]
 *
 * @since NEXT
 */
final class Term_Meta_Type extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type(): string {
        return 'term-meta';
    }

    /**
     * Returns the default shortcode attributes.
     *
     * @since NEXT
     *
     * @return array
     */
    protected function get_defaults(): array {
        return [
            'id'       => 0,
            'taxonomy' => '',
            'key'      => '',
            'single'   => '1',
            'before'   => '',
            'after'    => '',
            'fallback' => '',
            'format'   => '',
        ];
    }

    /**
     * Renders the shortcode.
     *
     * @since NEXT
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Enclosed content (optional).
     *
     * @return string
     */
    public function render( array $attributes, string $content ): string {
        // Merge with defaults.
        $attributes = $this->get_attributes( $attributes );

        // Parse dynamic attributes.
        $attributes = anys_parse_dynamic_attributes( $attributes );

        $meta_key  = isset( $attributes['key'] ) ? sanitize_key( (string) $attributes['key'] ) : '';
        $taxonomy  = isset( $attributes['taxonomy'] ) ? sanitize_key( (string) $attributes['taxonomy'] ) : '';
        $single_in = isset( $attributes['single'] ) ? strtolower( (string) $attributes['single'] ) : '1';

        // Resolve term ID.
        $term_id = isset( $attributes['id'] ) ? (int) $attributes['id'] : 0;

        if ( $term_id <= 0 ) {
            $queried = get_queried_object();
            if ( $queried instanceof \WP_Term ) {
                if ( $taxonomy === '' || $taxonomy === $queried->taxonomy ) {
                    $term_id  = (int) $queried->term_id;
                    $taxonomy = $taxonomy ?: $queried->taxonomy;
                }
            }
        }

        // Normalize "single" flag.
        $single = ! in_array( $single_in, [ '0', 'false', 'no' ], true );

        $value = '';

        if ( $term_id > 0 && $meta_key !== '' ) {
            $raw = get_term_meta( $term_id, $meta_key, $single );

            if ( is_array( $raw ) ) {
                // Implodes array meta values into a single string.
                $value = implode( ', ', array_map( 'strval', $raw ) );
            } else {
                $value = (string) $raw;
            }
        }

        // Apply formatting (date, number, etc.) if requested.
        $value = anys_format_value( $value, $attributes );

        // Wrap with before/after, apply fallback if empty.
        $output = anys_wrap_output( $value, $attributes );

        // Sanitize output.
        $output = wp_kses_post( $output );

        // Append processed inner content if present.
        if ( $content !== '' ) {
            $output .= do_shortcode( $content );
        }

        return $output;
    }
}
