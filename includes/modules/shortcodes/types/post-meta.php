<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Retrieves a post meta value and renders it.
 *
 * Handles the `[anys type="post-meta"]` shortcode.
 *
 * @since NEXT
 */
final class Post_Meta extends Base {
    use Singleton;

    public function get_type() {
        return 'post-meta';
    }

    protected function get_defaults() {
        return [
            'id'       => get_the_ID(),
            'name'     => '',
            'before'   => '',
            'after'    => '',
            'fallback' => '',
            'format'   => '',
        ];
    }

    /**
     * Renders the shortcode.
     *
     * @since 1.0.0
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

        $key     = $attributes['name'] ?? '';
        $post_id = (int) $attributes['id'];

        if ( $key === '' || $post_id <= 0 ) {
            return '';
        }

        // Fetch meta
        $value = get_post_meta( $post_id, $key, true );

        // Format and wrap
        $value  = anys_format_value( $value, $attributes );
        $output = anys_wrap_output( $value, $attributes );

        return wp_kses_post( (string) $output );
    }
}
