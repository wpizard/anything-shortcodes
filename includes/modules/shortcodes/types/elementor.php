<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Renders a saved Elementor template by ID and includes required assets.
 *
 * Handles the `[anys type="elementor" name="template" id="123"]` shortcode.
 *
 * @since NEXT
 */
final class Elementor extends Base {
    use Singleton;

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_type() {
        return 'elementor';
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
            'name'     => '',
            'id'       => 0,
            'before'   => '',
            'after'    => '',
            'fallback' => '',
            'format'   => '',
        ];
    }

    /**
     * Renders the shortcode.
     *
     * @since NEXT
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Enclosed content (optional).
     *
     * @return string
     */
    public function render( array $attributes, string $content ) {
        // Defaults merged.
        $attributes = $this->get_attributes( $attributes );

        // Dynamic attributes parsed.
        $attributes = anys_parse_dynamic_attributes( $attributes );

        // Provider validated.
        $provider_name = strtolower( (string) ( $attributes['name'] ?? '' ) );
        if ( $provider_name !== 'template' ) {
            return '';
        }

        // Elementor presence validated.
        if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
            $value  = esc_html__( 'Elementor is not active.', 'anys' );
            $output = anys_wrap_output( $value, $attributes );
            return wp_kses_post( (string) $output );
        }

        // ID validated.
        $template_id = (int) ( $attributes['id'] ?? 0 );
        if ( $template_id <= 0 ) {
            $value  = esc_html__( 'Missing or invalid "id" attribute.', 'anys' );
            $output = anys_wrap_output( $value, $attributes );
            return wp_kses_post( (string) $output );
        }

        // Template post fetched.
        $template_post = get_post( $template_id );
        if ( ! $template_post ) {
            $value  = esc_html__( 'Template not found.', 'anys' );
            $output = anys_wrap_output( $value, $attributes );
            return wp_kses_post( (string) $output );
        }

        // Post type validated.
        if ( get_post_type( $template_post ) !== 'elementor_library' ) {
            $value  = esc_html__( 'The provided ID is not an Elementor template.', 'anys' );
            $output = anys_wrap_output( $value, $attributes );
            return wp_kses_post( (string) $output );
        }

        // Assets enqueued when needed.
        $this->anys_maybe_enqueue_elementor_assets( $attributes );

        // Template type read.
        $template_type = get_post_meta( $template_id, '_elementor_template_type', true ); // 'section' | 'page'
        if ( $template_type && ! in_array( $template_type, [ 'section', 'page' ], true ) ) {
            $value  = esc_html__( 'Unsupported Elementor template type.', 'anys' );
            $output = anys_wrap_output( $value, $attributes );
            return wp_kses_post( (string) $output );
        }

        // Recursion prevented.
        if ( is_singular( 'elementor_library' ) && get_the_ID() === $template_id ) {
            $value  = esc_html__( 'Recursive rendering detected.', 'anys' );
            $output = anys_wrap_output( $value, $attributes );
            return wp_kses_post( (string) $output );
        }

        // Cache key built.
        $cache_key = sprintf(
            'anys_elem_tpl_%d_%s_%s',
            $template_id,
            (string) strtotime( $template_post->post_modified_gmt ),
            $template_type ? $template_type : 'na'
        );

        // Cached HTML returned if available (raw).
        $cached_html = get_transient( $cache_key );
        if ( $cached_html !== false ) {
            // Raw HTML is intentionally not escaped to keep Elementor markup intact.
            return anys_wrap_output( $cached_html, $attributes );
        }

        try {
            // Template rendered.
            $html = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, true );

            // Section wrapped when needed.
            if ( $template_type === 'section' ) {
                $html = '<div class="anys-elementor-section">' . $html . '</div>';
            }

            // Empty state handled.
            if ( ! $html ) {
                $value  = esc_html__( 'Template is empty or cannot be rendered.', 'anys' );
                $output = anys_wrap_output( $value, $attributes );
                return wp_kses_post( (string) $output );
            }

            // Cached for 10 minutes.
            set_transient( $cache_key, $html, MINUTE_IN_SECONDS * 10 );

            // Wrapped raw HTML returned.
            return anys_wrap_output( $html, $attributes );
        } catch ( \Throwable $e ) {
            $value  = esc_html__( 'An error occurred while rendering the template.', 'anys' );
            $output = anys_wrap_output( $value, $attributes );
            return wp_kses_post( (string) $output );
        }
    }

    /**
     * Conditionally enqueues Elementor assets for template rendering.
     *
     * @param array $attributes Shortcode attributes (merged and filtered).
     *
     * @since NEXT
     */
    private function anys_maybe_enqueue_elementor_assets( array $attributes ) : void {
        // Validate context.
        if ( 'elementor' !== strtolower( (string) ( $attributes['type'] ?? '' ) ) ) {
            return;
        }
        if ( 'template' !== strtolower( (string) ( $attributes['name'] ?? '' ) ) ) {
            return;
        }

        // Check Elementor.
        if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
            return;
        }

        // Validate template id.
        $id = isset( $attributes['id'] ) ? absint( $attributes['id'] ) : 0;
        if ( ! $id ) {
            return;
        }

        // Enqueue core frontend CSS/JS.
        wp_enqueue_style( 'elementor-frontend' );
        wp_enqueue_style( 'elementor-icons' );
        wp_enqueue_script( 'elementor-frontend' );

        // Enqueue Pro assets if available.
        if ( did_action( 'elementor_pro/init' ) ) {
            wp_enqueue_style( 'elementor-pro-frontend' );
            wp_enqueue_script( 'elementor-pro-frontend' );
        }

        // Let Elementor enqueue extra assets.
        $frontend = \Elementor\Plugin::instance()->frontend ?? null;
        if ( $frontend ) {
            if ( method_exists( $frontend, 'enqueue_styles' ) ) {
                $frontend->enqueue_styles();
            }
            if ( method_exists( $frontend, 'enqueue_scripts' ) ) {
                $frontend->enqueue_scripts();
            }
        }

        // Enqueue per-template CSS.
        if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
            try {
                $css = \Elementor\Core\Files\CSS\Post::create( $id );
                $css->enqueue();
            } catch ( \Throwable ) {
                // Silently ignored.
            }
        }
    }
}
