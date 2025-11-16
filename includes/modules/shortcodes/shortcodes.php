<?php

namespace AnyS\Modules;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Shortcodes module.
 *
 * Handles the `[anys]` shortcode registration and rendering.
 *
 * @since 1.0.0
 * @since 1.1.0 Changes file name.
 */
final class Shortcodes {
    use Singleton;

    /**
     * Adds hooks.
     *
     * @since 1.0.0
     */
    protected function add_hooks() {
        add_action( 'init', [ $this, 'register_shortcode' ] );
    }

    /**
     * Registers the `[anys]` shortcode.
     *
     * @since 1.0.0
     */
    public function register_shortcode() {
        // Requires the Base class before registering the shortcode.
        $base_file = ANYS_MODULES_PATH . 'shortcodes/types/base.php';

        if ( file_exists( $base_file ) ) {
            require_once $base_file;
        }

        add_shortcode( 'anys', [ $this, 'render_shortcode' ] );
    }

    /**
     * Renders the `[anys]` shortcode.
     *
     * @since 1.0.0
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Shortcode content.
     *
     * @return string
     */
    public function render_shortcode( array $attributes, string $content = '' ) {
        /**
         * Filters the shortcode attributes before processing.
         *
         * @since 1.0.0
         */
        $attributes = apply_filters(
            'anys/attributes',
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
                "anys/{$attributes['type']}/attributes",
                $attributes,
                $content
            );
        }

        // Bails early if no type or name is provided.
        if ( empty( $attributes['type'] ) && empty( $attributes['name'] ) ) {
            return '';
        }

        /**
         * Fires before rendering the shortcode output.
         *
         * @since 1.0.0
         */
        do_action(
            'anys/output/before',
            $attributes,
            $content
        );

        do_action(
            "anys/{$attributes['type']}/output/before",
            $attributes,
            $content
        );

        // Loads the matching handler file if it exists.
        $file = ANYS_MODULES_PATH . "shortcodes/types/{$attributes['type']}.php";

        if ( ! file_exists( $file ) ) {
            /**
             * Fires when the handler file is missing.
             *
             * @since 1.0.0
             */
            do_action(
                "anys/{$attributes['type']}/missing",
                $attributes,
                $content
            );

            return '';
        }

        require_once $file;

        // Build the class name based on the file type.
        $class_name = '\\AnyS\\Modules\\Shortcodes\\Types\\' . str_replace( '-', '_', ucfirst( $attributes['type'] ) . '_Type' );

        // Check if the class exists before calling render().
        if ( ! class_exists( $class_name ) ) {
            // Fires when the render method is missing.
            do_action(
                "anys/{$attributes['type']}/missing_class",
                $attributes,
                $content
            );

            return '';
        }

        $instance = $class_name::get_instance();

        if ( ! method_exists( $instance, 'render' ) ) {
            // Fires when the render method is missing.
            do_action(
                "anys/{$attributes['type']}/missing_render",
                $attributes,
                $content
            );

            return '';
        }

        // Captures the output.
        ob_start();

        echo $instance->render( $attributes, $content );

        $output = ob_get_clean();

        /**
         * Fires after rendering the shortcode output.
         *
         * @since 1.0.0
         */
        do_action(
            'anys/output/after',
            $attributes,
            $content
        );

        do_action(
            "anys/{$attributes['type']}/output/after",
            $attributes,
            $content
        );

        /**
         * Filters the final shortcode output.
         *
         * @since 1.0.0
         */
        $output = apply_filters(
            'anys/output',
            $output,
            $attributes,
            $content
        );

        $output = apply_filters(
            "anys/{$attributes['type']}/output",
            $output,
            $attributes,
            $content
        );

        // Returns the final output.
        return $output;
    }
}

/**
 * Initializes the module.
 *
 * @since 1.0.0
 */
Shortcodes::get_instance();
