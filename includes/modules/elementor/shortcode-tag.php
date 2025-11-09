<?php

namespace AnyS\Modules\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module as Dynamic_Tags_Module;

/**
 * Elementor Dynamic Tag for rendering shortcodes.
 *
 * @since NEXT
 */
final class Shortcode_Tag extends Tag {

    /**
     * Returns the tag slug.
     *
     * @return string
     */
    public function get_name() {
        return 'anys-shortcode';
    }

    /**
     * Returns the tag label shown in Elementor.
     *
     * @return string
     */
    public function get_title() {
        return esc_html__( 'Anything Shortcodes', 'anys' );
    }

    /**
     * Returns the tag group name.
     *
     * @return string
     */
    public function get_group() {
        return 'anything-shortcodes';
    }

    /**
     * Returns the tag categories.
     *
     * @return array
     */
    public function get_categories() {
        return [ Dynamic_Tags_Module::TEXT_CATEGORY ];
    }

    /**
     * Registers Elementor controls for this tag.
     *
     * @return void
     */
    protected function register_controls() {
        $this->add_control(
            'anys_shortcode',
            [
                'label'       => esc_html__( 'Shortcode', 'anys' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'placeholder' => '[anys type="post-field" name="post_title"]',
                'label_block' => true,
            ]
        );
    }

    /**
     * Renders the shortcode output.
     *
     * @return void
     */
    public function render() {
        $raw = trim( (string) $this->get_settings( 'anys_shortcode' ) );

        // Skip empty or invalid shortcode.
        if ( $raw === '' || ! preg_match( '/^\[[A-Za-z0-9_\-]+(?:\s+[^\]]+)?\]$/', $raw ) ) {
            return;
        }

        $shortcode = wp_kses_post( $raw );
        $output    = do_shortcode( $shortcode );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $output;
    }
}
