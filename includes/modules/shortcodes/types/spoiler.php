<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Defines the Spoiler shortcode type.
 *
 * Handles `[anys type="spoiler"]...[/anys]`.
 *
 * @since NEXT
 */
final class Spoiler_Type extends Base {
    use Singleton;

    /**
     * Returns the shortcode identifier.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type() {
        return 'spoiler';
    }

    /**
     * Provides default attributes.
     *
     * Supports all SpoilerJS options.
     *
     * @since NEXT
     *
     * @return array
     */
    protected function get_defaults() {
        return [
            'before' => '',
            'after' => '',
            'fallback' => '',
            'format' => '',
            'scale' => '',
            'min-velocity' => '',
            'max-velocity' => '',
            'particle-lifetime' => '',
            'density' => '',
            'reveal-duration' => '',
            'spawn-stop-delay' => '',
            'monitor-position' => '',
            'fps' => '',
        ];
    }

    /**
     * Generates the shortcode output.
     *
     * @since NEXT
     *
     * @param array  $attributes Raw shortcode attributes.
     * @param string $content    Shortcode content.
     *
     * @return string
     */
    public function render( array $attributes, string $content = '' ) {
        $attributes = $this->get_attributes( $attributes );
        $attributes = anys_parse_dynamic_attributes( $attributes );

        // Loads the SpoilerJS script on frontend.
        if ( ! is_admin() ) {
            wp_enqueue_script( 'anys-spoilerjs' );
        }

        // Builds attribute string.
        $keys = [
            'scale',
            'min-velocity',
            'max-velocity',
            'particle-lifetime',
            'density',
            'reveal-duration',
            'spawn-stop-delay',
            'monitor-position',
            'fps',
        ];

        $html_attrs = '';

        foreach ( $keys as $key ) {
            if ( ! isset( $attributes[ $key ] ) || $attributes[ $key ] === '' ) {
                continue;
            }

            $html_attrs .= sprintf(
                ' %s="%s"',
                esc_attr( $key ),
                esc_attr( (string) $attributes[ $key ] )
            );
        }

        // Processes content.
        $inner = wp_kses_post( do_shortcode( $content ) );

        // Creates <spoiler-span> element.
        $spoiler = sprintf(
            '<spoiler-span%s>%s</spoiler-span>',
            $html_attrs,
            $inner
        );

        // Applies wrappers and formatting.
        $spoiler = anys_format_value( $spoiler, $attributes );
        $output = anys_wrap_output( $spoiler, $attributes );

        // Allows spoiler-span attributes.
        $allowed = wp_kses_allowed_html( 'post' );
        $allowed['spoiler-span'] = [
            'scale' => true,
            'min-velocity' => true,
            'max-velocity' => true,
            'particle-lifetime' => true,
            'density' => true,
            'reveal-duration' => true,
            'spawn-stop-delay' => true,
            'monitor-position' => true,
            'fps' => true,
        ];

        return wp_kses( (string) $output, $allowed );
    }
}
