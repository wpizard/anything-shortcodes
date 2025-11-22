<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Video shortcode type.
 *
 * Handles the `[anys type="video"]` shortcode.
 *
 * @since NEXT
 */
final class Video_Type extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type(): string {
        return 'video';
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
            'src'           => '',
            'attachment_id' => '',
            'poster'        => '',
            'autoplay'      => 'false',
            'loop'          => 'false',
            'muted'         => 'false',
            'controls'      => 'true',
            'preload'       => 'metadata',
            'width'         => '',
            'height'        => '',
            'class'         => '',
            'id'            => '',
            'playsinline'   => 'true',
            'before'        => '',
            'after'         => '',
            'fallback'      => '',
            'format'        => '',
        ];
    }

    /**
     * Renders the shortcode.
     *
     * @since NEXT
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Enclosed content (unused).
     *
     * @return string
     */
    public function render( array $attributes, string $content ): string {
        // Retrieves attributes merged with defaults.
        $attributes = $this->get_attributes( $attributes );

        // Parses dynamic attributes.
        if ( function_exists( 'anys_parse_dynamic_attributes' ) ) {
            $attributes = anys_parse_dynamic_attributes( $attributes );
        }

        // Resolves attachment ID to URL when provided.
        $attachment_id  = isset( $attributes['attachment_id'] ) && is_numeric( $attributes['attachment_id'] )
            ? (int) $attributes['attachment_id']
            : 0;
        $attachment_url = $attachment_id > 0 ? wp_get_attachment_url( $attachment_id ) : '';

        if ( $attachment_url ) {
            $attributes['src'] = $attachment_url;
        }

        $src = isset( $attributes['src'] ) ? trim( (string) $attributes['src'] ) : '';

        // Stops when no video source exists.
        if ( $src === '' ) {
            return '';
        }

        // Handles blocked hosts.
        $is_blocked = $this->is_blocked_video_host( $src );

        if ( $is_blocked && ! is_admin() ) {
            return '';
        }

        if ( $is_blocked && is_admin() ) {
            return '<span class="anys-video-error">' .
                esc_html__( 'Social video URLs are not supported by the video shortcode.', 'anys' ) .
            '</span>';
        }

        $src = esc_url( $src );

        $video_attributes = [];

        // Adds ID attribute.
        if ( ! empty( $attributes['id'] ) ) {
            $video_attributes[] = 'id="' . esc_attr( $attributes['id'] ) . '"';
        }

        // Adds class attribute.
        $classes = [ 'anys-video' ];
        if ( ! empty( $attributes['class'] ) ) {
            $classes[] = trim( (string) $attributes['class'] );
        }
        $video_attributes[] = 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';

        // Adds src attribute.
        $video_attributes[] = 'src="' . $src . '"';

        // Adds poster attribute.
        if ( ! empty( $attributes['poster'] ) ) {
            $video_attributes[] = 'poster="' . esc_url( $attributes['poster'] ) . '"';
        }

        // Adds dimensions.
        if ( isset( $attributes['width'] ) && is_numeric( $attributes['width'] ) ) {
            $video_attributes[] = 'width="' . (int) $attributes['width'] . '"';
        }

        if ( isset( $attributes['height'] ) && is_numeric( $attributes['height'] ) ) {
            $video_attributes[] = 'height="' . (int) $attributes['height'] . '"';
        }

        // Adds boolean attributes.
        foreach ( [ 'controls', 'autoplay', 'loop', 'muted', 'playsinline' ] as $bool_key ) {
            if ( isset( $attributes[ $bool_key ] ) && $this->is_true( $attributes[ $bool_key ] ) ) {
                $video_attributes[] = $bool_key;
            }
        }

        // Adds preload attribute.
        if ( isset( $attributes['preload'] ) ) {
            $preload = strtolower( trim( (string) $attributes['preload'] ) );
            if ( in_array( $preload, [ 'auto', 'metadata', 'none' ], true ) ) {
                $video_attributes[] = 'preload="' . esc_attr( $preload ) . '"';
            }
        }

        $attr_string = implode( ' ', $video_attributes );

        // Builds the video element.
        $value  = '<video ' . $attr_string . '>';
        $value .= esc_html__( 'Your browser does not support HTML5 video.', 'anys' );
        $value .= '</video>';

        // Wraps with before/after and fallback.
        $output = anys_wrap_output( $value, $attributes );

        // Outputs inline responsive styling.
        $this->maybe_output_inline_css();

        // Returns sanitized output.
        return wp_kses_post( (string) $output );
    }

    /**
     * Checks whether the video URL belongs to a blocked host.
     *
     * @since NEXT
     *
     * @param string $url Video URL.
     *
     * @return bool
     */
    private function is_blocked_video_host( $url ): bool {
        $host = wp_parse_url( $url, PHP_URL_HOST );

        if ( ! is_string( $host ) || $host === '' ) {
            return false;
        }

        $host    = strtolower( $host );
        $blocked = [
            'youtube.com',
            'www.youtube.com',
            'youtu.be',
            'vimeo.com',
            'www.vimeo.com',
            'dailymotion.com',
            'www.dailymotion.com',
        ];

        foreach ( $blocked as $domain ) {
            $length   = strlen( (string) $domain );
            $is_exact = ( $host === $domain );
            $is_sub   = $length > 0 && substr( $host, - ( $length + 1 ) ) === '.' . $domain;

            if ( $is_exact || $is_sub ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalizes boolean-like attribute values.
     *
     * @since NEXT
     *
     * @param mixed $value Raw attribute value.
     *
     * @return bool
     */
    private function is_true( $value ): bool {
        if ( is_bool( $value ) ) {
            return $value;
        }

        $value = strtolower( trim( (string) $value ) );

        return in_array(
            $value,
            [ '1', 'true', 'yes', 'on' ],
            true
        );
    }

    /**
     * Outputs the inline responsive CSS only once.
     *
     * @since NEXT
     */
    private function maybe_output_inline_css(): void {
        static $done = false;

        if ( $done ) {
            return;
        }

        $done = true;

        echo '<style>
            .anys-video {
                max-width: 100%;
                height: auto;
                display: block;
            }
        </style>';
    }
}
