<?php

namespace AnyS\Shortcodes;

defined( 'ABSPATH' ) or die();

/**
 * Registers the main [anys] shortcode.
 *
 * @since 1.0.0
 */
final class Register {

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
     * Adds WordPress hooks.
     *
     * @since 1.0.0
     */
    protected function add_hooks() {
        add_action( 'init', [ $this, 'register_shortcodes' ] );
    }

    /**
     * Registers the shortcode.
     *
     * @since 1.0.0
     */
    public function register_shortcodes() {
        add_shortcode( 'anys', [ $this, 'render_shortcode' ] );
    }

    /**
     * Renders the [anys] shortcode.
     *
     * @since 1.0.0
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Shortcode content.
     *
     * @return string
     */
    public function render_shortcode( $attributes, $content ) {
        // Default attributes.
        $defaults = [
            'type'     => '',
            'name'     => '',
            'id'       => '',
            'before'   => '',
            'after'    => '',
            'fallback' => '',
            'format'   => '',
        ];

        $attributes = shortcode_atts( $defaults, $attributes, 'anys' );

        /**
         * Filters the shortcode attributes before processing.
         *
         * @since 1.0.0
         */
        $attributes = apply_filters(
            'anys/shortcodes/attributes',
            $attributes,
            $content
        );

        /**
         * Dynamic filter for attributes by type.
         *
         * @since 1.0.0
         */
        if ( ! empty( $attributes['type'] ) ) {
            $attributes = apply_filters(
                "anys/shortcodes/{$attributes['type']}/attributes",
                $attributes,
                $content
            );
        }

        // Bails early if no type or name is provided.
        if ( empty( $attributes['type'] ) || empty( $attributes['name'] ) ) {
            return '';
        }

        /**
         * Fires before rendering the shortcode output.
         *
         * @since 1.0.0
         */
        do_action(
            'anys/shortcodes/output/before',
            $attributes,
            $content
        );

        do_action(
            "anys/shortcodes/{$attributes['type']}/output/before",
            $attributes,
            $content
        );

        ob_start();

        // Loads the matching handler file if it exists.
        $file_name = str_replace( '_', '-', $attributes['type'] );
        $file      = ANYS_SHORTCODES_PATH . "{$file_name}.php";

        if ( file_exists( $file ) ) {
            require $file;
        } else {
            /**
             * Fires when the handler file is missing.
             *
             * @since 1.0.0
             */
            do_action(
                "anys/shortcodes/{$attributes['type']}/missing",
                $attributes,
                $content
            );
        }

        $output = ob_get_clean();

        /**
         * Fires after rendering the shortcode output.
         *
         * @since 1.0.0
         */
        do_action(
            'anys/shortcodes/output/after',
            $attributes,
            $content
        );

        do_action(
            "anys/shortcodes/{$attributes['type']}/output/after",
            $attributes,
            $content
        );

        /**
         * Filters the final shortcode output.
         *
         * @since 1.0.0
         */
        $output = apply_filters(
            'anys/shortcodes/output',
            $output,
            $attributes,
            $content
        );

        $output = apply_filters(
            "anys/shortcodes/{$attributes['type']}/output",
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
