<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Audio shortcode type.
 *
 * Handles the `[anys type="audio"]` shortcode.
 *
 * @since NEXT
 */
final class Audio_Type extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type(): string {
        return 'audio';
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
            'autoplay'      => 'false',
            'loop'          => 'false',
            'muted'         => 'false',
            'controls'      => 'true',
            'preload'       => 'metadata',
            'class'         => '',
            'id'            => '',
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

        // Stops when no audio source exists.
        if ( $src === '' ) {
            return '';
        }

        // Handles blocked hosts.
        $is_blocked = $this->is_blocked_audio_host( $src );

        if ( $is_blocked && ! is_admin() ) {
            return '';
        }

        if ( $is_blocked && is_admin() ) {
            return '<span class="anys-audio-error">' .
                esc_html__( 'Streaming audio URLs are not supported by the audio shortcode.', 'anys' ) .
            '</span>';
        }

        $src = esc_url( $src );

        $audio_attributes = [];

        // Adds ID attribute.
        if ( ! empty( $attributes['id'] ) ) {
            $audio_attributes[] = 'id="' . esc_attr( $attributes['id'] ) . '"';
        }

        // Adds class attribute.
        $classes = [ 'anys-audio' ];
        if ( ! empty( $attributes['class'] ) ) {
            $classes[] = trim( (string) $attributes['class'] );
        }
        $audio_attributes[] = 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';

        // Adds src attribute.
        $audio_attributes[] = 'src="' . $src . '"';

        // Adds boolean attributes.
        foreach ( [ 'controls', 'autoplay', 'loop', 'muted' ] as $bool_key ) {
            if ( isset( $attributes[ $bool_key ] ) && $this->is_true( $attributes[ $bool_key ] ) ) {
                $audio_attributes[] = $bool_key;
            }
        }

        // Adds preload attribute.
        if ( isset( $attributes['preload'] ) ) {
            $preload = strtolower( trim( (string) $attributes['preload'] ) );
            if ( in_array( $preload, [ 'auto', 'metadata', 'none' ], true ) ) {
                $audio_attributes[] = 'preload="' . esc_attr( $preload ) . '"';
            }
        }

        $attr_string = implode( ' ', $audio_attributes );

        // Builds the audio element.
        $value  = '<audio ' . $attr_string . '>';
        $value .= esc_html__( 'Your browser does not support HTML5 audio.', 'anys' );
        $value .= '</audio>';

        // Wraps with before/after and fallback.
        if ( function_exists( 'anys_wrap_output' ) ) {
            $output = anys_wrap_output( $value, $attributes );
        } else {
            $output = $value;
        }

        // Returns sanitized output.
        return wp_kses_post( (string) $output );
    }

    /**
     * Checks whether the audio URL belongs to a blocked host.
     *
     * @since NEXT
     *
     * @param string $url Audio URL.
     *
     * @return bool
     */
    private function is_blocked_audio_host( $url ): bool {
        $host = wp_parse_url( $url, PHP_URL_HOST );

        if ( ! is_string( $host ) || $host === '' ) {
            return false;
        }

        $host    = strtolower( $host );
        $blocked = [
            'soundcloud.com',
            'www.soundcloud.com',
            'spotify.com',
            'www.spotify.com',
            'open.spotify.com',
            'music.apple.com',
            'deezer.com',
            'www.deezer.com',
            'tidal.com',
            'www.tidal.com',
            'pandora.com',
            'www.pandora.com',
            'mixcloud.com',
            'www.mixcloud.com',
            'audiomack.com',
            'www.audiomack.com',
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
}
