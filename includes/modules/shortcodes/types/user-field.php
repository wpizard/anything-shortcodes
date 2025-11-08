<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Retrieves a standard user field and renders it.
 *
 * Handles the `[anys type="user-field"]` shortcode.
 *
 * @since NEXT
 */
final class User_Field extends Base {
    use Singleton;

    public function get_type() {
        return 'user-field';
    }

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
        // Parse dynamic attributes
        $attributes = anys_parse_dynamic_attributes( $attributes );
        $attributes = $this->get_attributes( $attributes );

        $key     = $attributes['name'] ?? '';
        $user_id = (int) $attributes['id'];

        if ( $key === '' || $user_id <= 0 ) {
            return '';
        }

        // Fetch user and field
        $user  = get_userdata( $user_id );
        $value = ( $user && isset( $user->$key ) ) ? $user->$key : '';

        // Format and wrap
        $value  = anys_format_value( $value, $attributes );
        $output = anys_wrap_output( $value, $attributes );

        return wp_kses_post( (string) $output );
    }
}
