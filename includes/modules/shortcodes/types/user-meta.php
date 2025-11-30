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
    public function get_type(): string {
        return 'user-meta';
    }

    /**
     * Returns the default shortcode attributes.
     *
     * @since NEXT
     *
     * @return array<string,mixed>
     */
    protected function get_defaults(): array {
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
     * @param array<string,mixed> $attributes Shortcode attributes.
     * @param string|null         $content    Enclosed content (optional).
     *
     * @return string
     */
    public function render( array $attributes, ?string $content = '' ): string {
        $attributes = $this->get_attributes( $attributes );

        // Parses dynamic attributes.
        $attributes = anys_parse_dynamic_attributes( $attributes );

        $key     = isset( $attributes['name'] ) ? (string) $attributes['name'] : '';
        $user_id = isset( $attributes['id'] ) ? (int) $attributes['id'] : 0;

        if ( $key === '' || $user_id <= 0 ) {
            return '';
        }

        // Fetches user meta.
        $value = get_user_meta( $user_id, $key, true );

        // Formats and wraps.
        $value  = anys_format_value( $value, $attributes );
        $output = anys_wrap_output( $value, $attributes );

        return wp_kses_post( (string) $output );
    }
}
