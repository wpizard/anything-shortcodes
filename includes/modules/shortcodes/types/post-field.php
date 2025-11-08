<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;
use AnyS\Modules\Shortcodes\Types\Base;

/**
 * Post Field shortcode type.
 *
 * Handles the `[anys type="post-field"]` shortcode.
 *
 * @since NEXT
 */
final class Post_Field extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type() {
        return 'post-field';
    }

    /**
     * Returns default attributes.
     *
     * @since NEXT
     *
     * @return array
     */
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
        $attributes = $this->get_attributes( $attributes );

        // Parses dynamic attributes first (security, caching, etc.).
        $attributes = anys_parse_dynamic_attributes( $attributes );

        $key     = $attributes['name'];
        $post_id = intval( $attributes['id'] );

        $post  = get_post( $post_id );
        $value = ( $post && isset( $post->$key ) ) ? $post->$key : '';

        // Formats the value if needed.
        $value = anys_format_value( $value, $attributes );

        // Wraps with before/after and applies fallback.
        $output = anys_wrap_output( $value, $attributes );

        // Outputs the sanitized content.
        return wp_kses_post( $output ) . do_shortcode( $content );
    }
}

/**
 * Initializes the module.
 *
 * @since NEXT
 */
Post_Field::get_instance();
