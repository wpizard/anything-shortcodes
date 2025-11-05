<?php
/**
 * Dynamic Tag: Shortcode.
 *
 * @since NEXT
 */

namespace AnyS\Elementor\Tags;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module as Dynamic_Tags_Module;

class Shortcode_Tag extends Tag {

    /**
     * Gets unique slug.
     *
     * @since NEXT
     * 
     * @return string
     */
    public function get_name() {
        return 'anything-shortcode';
    }

    /**
     * Gets title.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_title() {
        return __( 'Shortcode', 'anys' );
    }

    /**
     * Gets group key.
     *
     * @since NEXT
     *
     * @return string
     */
    public function get_group() {
        return 'anything-shortcodes';
    }

    /**
     * Gets categories.
     *
     * @since NEXT
     *
     * @return array
     */
    public function get_categories() {
        return [ Dynamic_Tags_Module::TEXT_CATEGORY ];
    }

    /**
     * Registers controls.
     *
     * @since NEXT
     * @return void
     */
    protected function register_controls() {
        $this->add_control(
            'shortcode',
            [
                /* Translators: Elementor control label. */
                'label'       => __( 'Shortcode', 'anys' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => '[my_shortcode attr="value"]',
                'label_block' => true,
            ]
        );
    }

    /**
     * Renders output.
     *
     * @since NEXT
     *
     * @return void
     */
    public function render() {
        $raw = trim((string) $this->get_settings('shortcode'));

        if ($raw === '') {
            return;
        }

        if (!preg_match('/^\[[A-Za-z0-9_\-]+(?:\s+[^\]]+)?\]$/', $raw)) {
            return;
        }

        $shortcode = wp_kses_post($raw);
        $output    = do_shortcode($shortcode);

        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
