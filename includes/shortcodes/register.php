<?php

namespace AnyS\Shortcodes;

defined( 'ABSPATH' ) or die();

/**
 * Registers Shortcodes class.
 *
 * @since 1.0.0
 */
final class Register {

    /**
     * The plugin instance.
     *
     * @since 1.0.0
     */
    private $plugin;

    /**
     * The instance.
     *
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Returns the instance.
     *
     * @since 1.0.0
     *
     * @return Register
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->add_hooks();
    }

    /**
     * Adds hooks.
     *
     * @since 1.0.0
     */
    protected function add_hooks() {
        add_action( 'init', [ $this, 'register_shortcodes' ] );
    }

    /**
     * Registers shortcodes.
     *
     * @since 1.0.0
     */
    public function register_shortcodes() {
        add_shortcode( 'anys', [ $this, 'render_shortcode' ] );
    }

    /**
     * Renders shortcodes.
     *
     * @since 1.0.0
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Shortcode content.
     */
    public function render_shortcode( $attributes, $content ) {

        /**
         * Filters the attributes of the shortcodes.
         *
         * @since 1.0.0
         *
         * @param array  $attributes Shortcode attributes.
         * @param string $content    Shortcode content.
         */
        $attributes = apply_filters(
            'anys/shortcodes/attributes',
            $attributes,
            $content
        );

        /**
         * Filters the attributes of the a shortcode.
         *
         * The dynamic portion of the hook name, `$attributes['name']`, refers to
         * the shortcode name.
         *
         * Possible hook names include:
         *
         *  - `anys/shortcodes/post-field/attributes`
         *  - `anys/shortcodes/post-custom-fields/attributes`
         *
         * @since 1.0.0
         *
         * @param array  $attributes Shortcode attributes.
         * @param string $content    Shortcode content.
         */
        $attributes = apply_filters(
            "anys/shortcodes/{$attributes['name']}/attributes",
            $attributes,
            $content
        );

        /**
         * Filters the content of the shortcodes.
         *
         * @since 1.0.0
         *
         * @param string $content    Shortcode content.
         * @param array  $attributes Shortcode attributes.
         */
        $content = apply_filters(
            'anys/shortcodes/content',
            $content,
            $attributes
        );

        /**
         * Filters the content of the a shortcode.
         *
         * The dynamic portion of the hook name, `$attributes['name']`, refers to
         * the shortcode name.
         *
         * Possible hook names include:
         *
         *  - `anys/shortcodes/post-field/content`
         *  - `anys/shortcodes/post-custom-fields/content`
         *
         * @since 1.0.0
         *
         * @param string $content    Shortcode content.
         * @param array  $attributes Shortcode attributes.
         */
        $content = apply_filters(
            "anys/shortcodes/{$attributes['name']}/content",
            $content,
            $attributes
        );

        // Bails early if 'name' attribute is missing.
        if ( empty( $attributes['name'] ) ) {
            return;
        }

        ob_start();

        /**
         * Fires before the output of the shortcodes.
         *
         * @since 1.0.0
         *
         * @param string $content    Shortcode content.
         * @param array  $attributes Shortcode attributes.
         */
        do_action(
            "anys/shortcodes/output/before",
            $attributes,
            $content
        );

        /**
         * Fires before the output of a shortcode.
         *
         * The dynamic portion of the hook name, `$attributes['name']`, refers to
         * the shortcode name.
         *
         * Possible hook names include:
         *
         *  - `anys/shortcodes/post-field/output/before`
         *  - `anys/shortcodes/post-custom-fields/output/before`
         *
         * @since 1.0.0
         *
         * @param string $content    Shortcode content.
         * @param array  $attributes Shortcode attributes.
         */
        do_action(
            "anys/shortcodes/{$attributes['name']}/output/before",
            $attributes,
            $content
        );

        require ANYS_INCLUDES_PATH . "shortcodes/{$attributes['name']}.php";

        /**
         * Fires after the output of the shortcodes.
         *
         * @since 1.0.0
         *
         * @param string $content    Shortcode content.
         * @param array  $attributes Shortcode attributes.
         */
        do_action(
            "anys/shortcodes/output/after",
            $attributes,
            $content
        );

        /**
         * Fires after the output of a shortcode.
         *
         * The dynamic portion of the hook name, `$attributes['name']`, refers to
         * the shortcode name.
         *
         * Possible hook names include:
         *
         *  - `anys/shortcodes/post-field/output/after`
         *  - `anys/shortcodes/post-custom-fields/output/after`
         *
         * @since 1.0.0
         *
         * @param string $content    Shortcode content.
         * @param array  $attributes Shortcode attributes.
         */
        do_action(
            "anys/shortcodes/{$attributes['name']}/output/after",
            $attributes,
            $content
        );

        $output = ob_get_clean();

        /**
         * Filters the output of the shortcodes.
         *
         * @since 1.0.0
         *
         * @param string $output     Shortcode output.
         * @param array  $attributes Shortcode attributes.
         * @param string $content    Shortcode content.
         */
        $output = apply_filters(
            "anys/shortcodes/output",
            $output,
            $attributes,
            $content
        );

        /**
         * Filters the output of the a shortcode.
         *
         * The dynamic portion of the hook name, `$attributes['name']`, refers to
         * the shortcode name.
         *
         * Possible hook names include:
         *
         *  - `anys/shortcodes/post-field/output`
         *  - `anys/shortcodes/post-custom-fields/output`
         *
         * @since 1.0.0
         *
         * @param string $output     Shortcode output.
         * @param array  $attributes Shortcode attributes.
         * @param string $content    Shortcode content.
         */
        $output = apply_filters(
            "anys/shortcodes/{$attributes['name']}/output",
            $output,
            $attributes,
            $content
        );

        return $output . do_shortcode( $content );
    }
}

/**
 * Initializes the class.
 *
 * @since 1.0.0
 */
Register::get_instance();
