<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Retrieves a WordPress option value and renders it.
 *
 * Handles the `[anys type="option"]` shortcode.
 *
 * @since NEXT
 */
final class Option extends Base {
    use Singleton;

    public function get_type() {
        return 'option';
    }

    protected function get_defaults() {
        return [
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
    public function render( array $attributes, string $content = '' ) {
        // Parse dynamic attributes
        $attributes = anys_parse_dynamic_attributes( $attributes );
        $attributes = $this->get_attributes( $attributes );

        // Resolve option key
        $key = $attributes['name'] ?? '';
        if ( $key === '' ) {
            return '';
        }

        // Fetch option
        $value = get_option( $key, '' );

        // Format and wrap
        $value  = anys_format_value( $value, $attributes );
        $output = anys_wrap_output( $value, $attributes );

        // Return sanitized output
        return wp_kses_post( (string) $output );
    }
}
