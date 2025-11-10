<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Retrieves a user meta value and renders it.
 *
 * Handles the `[anys type="user-meta"]` shortcode.
 *
 * @since NEXT
 */
final class User_Meta_Type extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type() {
        return 'user-meta';
    }

    /**
     * Returns the default shortcode attributes.
     *
     * @since NEXT
     *
     * @return array
     */
    protected function get_defaults() {
        return [
            'id'       => get_current_user_id(),
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

        // Parses dynamic attributes.
        $attributes = anys_parse_dynamic_attributes( $attributes );

        $user_meta_key = $attributes['name'] ?? '';
        $user_id       = (int) $attributes['id'];

        if ( $user_meta_key === '' || $user_id <= 0 ) {
            return '';
        }

        // Fetches user meta.
        $user_meta_value = get_user_meta( $user_id, $user_meta_key, true );

        // Formats and wraps.
        $formatted_value = anys_format_value( $user_meta_value, $attributes );
        $output          = anys_wrap_output( $formatted_value, $attributes );

        return wp_kses_post( (string) $output );
    }
}
