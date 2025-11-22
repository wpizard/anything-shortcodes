<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Embed shortcode type.
 *
 * Handles the `[anys type="embed"]` shortcode.
 *
 * @since NEXT
 */
final class Embed_Type extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type(): string {
        return 'embed';
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
            'url'      => '',
            'before'   => '',
            'after'    => '',
            'fallback' => '',
            'format'   => '',
        ];
    }

    /**
     * Renders the shortcode output.
     *
     * @since NEXT
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Enclosed content (optional).
     *
     * @return string
     */
    public function render( array $attributes, string $content ): string {
        // Retrieves merged shortcode attributes.
        $attributes = $this->get_attributes( $attributes );

        // Resolves dynamic placeholder attributes.
        $attributes = anys_parse_dynamic_attributes( $attributes );

        $url = isset( $attributes['url'] ) ? trim( (string) $attributes['url'] ) : '';

        // Returns early if no URL is provided.
        if ( '' === $url ) {
            return '';
        }

        // Attempts to fetch an oEmbed preview for the URL.
        $embed_html = wp_oembed_get( $url );

        // Applies fallback rendering if oEmbed fails.
        if ( ! $embed_html ) {
            $fallback = isset( $attributes['fallback'] ) ? (string) $attributes['fallback'] : '';

            if ( '' !== $fallback ) {
                $embed_html = $fallback;
            } else {
                // Uses a basic anchor link as the final fallback.
                $embed_html = sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                    esc_url( $url ),
                    esc_html( $url )
                );
            }
        }

        $value = $embed_html;

        // Wraps the value with before/after markup and fallback handling.
        $output = anys_wrap_output( $value, $attributes );

        // Returns the rendered content and processes nested shortcodes.
        return $output . do_shortcode( $content );
    }
}
