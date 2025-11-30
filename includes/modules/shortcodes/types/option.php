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
final class Option_Type extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type(): string {
        return 'option';
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

        // Resolves option key.
        $key = $attributes['name'] ?? '';
        if ( $key === '' ) {
            return '';
        }

        // Fetches option.
        $value = get_option( $key, '' );

        // Formats and wraps.
        $value  = anys_format_value( $value, $attributes );
        $output = anys_wrap_output( $value, $attributes );

        // Returns sanitized output.
        return wp_kses_post( (string) $output );
    }
}
