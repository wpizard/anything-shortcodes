<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Represents the QR shortcode type.
 *
 * Handles `[anys type="qr" ...]`.
 *
 * @since NEXT
 */
final class Qr_Type extends Base {
    use Singleton;

    /**
     * Returns the shortcode identifier.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type(): string {
        return 'qr';
    }

    /**
     * Provides default shortcode attributes.
     *
     * @since NEXT
     *
     * @return array
     */
    protected function get_defaults(): array {
        return [
            'text'                    => '',
            'before'                  => '',
            'after'                   => '',
            'fallback'                => '',
            'format'                  => '',
            'size'                    => 200,
            'class'                   => 'anys-qr',
            'style'                   => '',
            'module_color'            => '',
            'position_ring_color'     => '',
            'position_center_color'   => '',
            'animation'               => '',
        ];
    }

    /**
     * Renders the shortcode output.
     *
     * @since NEXT
     *
     * @param array  $attributes Parsed shortcode attributes.
     * @param string $content    Inner shortcode content.
     *
     * @return string
     */
    public function render( array $attributes, string $content = '' ): string {
        // Resolves merged attributes.
        $attributes = $this->get_attributes( $attributes );
        $attributes = anys_parse_dynamic_attributes( $attributes );

        // Enqueues frontend scripts.
        if ( ! is_admin() ) {

            // Base QR script.
            wp_enqueue_script( 'anys-qr-code' );

            // Animation script if needed.
            if ( ! empty( $attributes['animation'] ) ) {
                wp_enqueue_script( 'anys-qr-animation' );
            }
        }


        // Resolves QR contents.
        $resolved_contents = '';

        if ( ! empty( $attributes['text'] ) ) {
            $resolved_contents = trim( (string) $attributes['text'] );
        } elseif ( '' !== $content ) {
            $raw_contents      = do_shortcode( $content );
            $raw_contents      = wp_strip_all_tags( $raw_contents );
            $resolved_contents = trim( $raw_contents );
        }

        if ( '' === $resolved_contents ) {
            return '';
        }

        // Resolves size attribute.
        $qr_size_value = isset( $attributes['size'] )
            ? (int) $attributes['size']
            : 200;

        // Resolves class name.
        $qr_class_value = isset( $attributes['class'] )
            ? trim( (string) $attributes['class'] )
            : '';

        if ( '' !== $qr_class_value ) {
            $qr_class_value = sanitize_html_class( $qr_class_value );
        }

        // Builds style attribute.
        $style_input_value     = isset( $attributes['style'] ) ? (string) $attributes['style'] : '';
        $style_attributes_list = [
            "width: {$qr_size_value}px",
            "height: {$qr_size_value}px",
        ];

        if ( '' !== trim( $style_input_value ) ) {
            $style_attributes_list[] = trim( $style_input_value );
        }

        $qr_style_attribute = implode( '; ', $style_attributes_list );

        // Resolves optional color attributes.
        $module_color_value          = (string) ( $attributes['module_color'] ?? '' );
        $position_ring_color_value   = (string) ( $attributes['position_ring_color'] ?? '' );
        $position_center_color_value = (string) ( $attributes['position_center_color'] ?? '' );

        // Resolves animation attribute.
        $animation_value = trim( (string) ( $attributes['animation'] ?? '' ) );

        // Builds QR element attributes.
        $qr_code_attributes = [
            'contents' => $resolved_contents,
            'style'    => $qr_style_attribute,
        ];

        if ( '' !== $qr_class_value ) {
            $qr_code_attributes['class'] = $qr_class_value;
        }

        if ( '' !== $module_color_value ) {
            $qr_code_attributes['module-color'] = $module_color_value;
        }

        if ( '' !== $position_ring_color_value ) {
            $qr_code_attributes['position-ring-color'] = $position_ring_color_value;
        }

        if ( '' !== $position_center_color_value ) {
            $qr_code_attributes['position-center-color'] = $position_center_color_value;
        }

        if ( '' !== $animation_value ) {
            $qr_code_attributes['data-anys-qr-animation'] = $animation_value;
        }

        // Builds attribute markup.
        $attributes_html = '';

        foreach ( $qr_code_attributes as $attribute_name => $attribute_value ) {
            if ( '' === $attribute_value ) {
                continue;
            }

            $attributes_html .= sprintf(
                ' %s="%s"',
                esc_attr( $attribute_name ),
                esc_attr( (string) $attribute_value )
            );
        }

        // Creates the QR element.
        $qr_element = sprintf(
            '<qr-code%s></qr-code>',
            $attributes_html
        );

        // Applies formatting wrappers.
        $qr_element = anys_format_value( $qr_element, $attributes );
        $final_output = anys_wrap_output( $qr_element, $attributes );

        // Extends allowed kses attributes.
        $allowed_html = wp_kses_allowed_html( 'post' );

        $allowed_html['qr-code'] = [
            'class'                  => true,
            'style'                  => true,
            'contents'               => true,
            'module-color'           => true,
            'position-ring-color'    => true,
            'position-center-color'  => true,
            'data-anys-qr-animation' => true,
        ];

        return wp_kses( (string) $final_output, $allowed_html );
    }
}
