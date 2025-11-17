<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Retrieves a core term meta value and renders it.
 *
 * Handles the `[anys type="term-meta"]` shortcode.
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
        // Merges attributes.
        $attributes = $this->get_attributes( $attributes );

        // Parses dynamic attributes.
        $attributes = anys_parse_dynamic_attributes( $attributes );

        $meta_key  = sanitize_key( $attributes['key'] );
        $taxonomy  = sanitize_key( $attributes['taxonomy'] );
        $single_in = strtolower( $attributes['single'] );

        // Resolves term ID.
        $term_id = (int) $attributes['id'];

        if ( $term_id <= 0 ) {
            $queried = get_queried_object();

            if ( $queried instanceof \WP_Term ) {
                if ( $taxonomy === '' || $taxonomy === $queried->taxonomy ) {
                    $term_id = (int) $queried->term_id;

                    if ( $taxonomy === '' ) {
                        $taxonomy = $queried->taxonomy;
                    }
                }
            }
        }

        // Normalizes single flag.
        $single = ! in_array( $single_in, [ '0', 'false', 'no' ], true );

        $value = '';

        if ( $term_id > 0 && $meta_key !== '' ) {
            $raw = get_term_meta( $term_id, $meta_key, $single );

            if ( is_array( $raw ) ) {
                // Implodes array values.
                $value = implode( ', ', array_map( 'strval', $raw ) );
            } else {
                $value = (string) $raw;
            }
        }

        // Formats value.
        $value = anys_format_value( $value, $attributes );

        // Wraps output.
        $output = anys_wrap_output( $value, $attributes );

        // Sanitizes output.
        $output = wp_kses_post( $output );

        // Appends content.
        if ( $content !== '' ) {
            $output .= do_shortcode( $content );
        }

        return $output;
    }
}
