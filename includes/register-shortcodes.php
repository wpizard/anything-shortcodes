<?php

namespace AnyS;

defined( 'ABSPATH' ) or die();

/**
 * Registers the shortcodes.
 *
 * @since 1.0.0
 * @since 1.1.0 Changes file name.
 */
final class Register_Shortcodes {

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
     * @return Register_Shortcodes
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
     * Registers Shortcodes the shortcode.
     *
     * @since 1.0.0
     */
    public function register_shortcodes() {
        add_shortcode( 'anys', [ $this, 'render_shortcode' ] );
    }

    /**
     * Renders the shortcode.
     *
     * @since 1.0.0
     * @since 1.1.0 Changes hook names format.
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Shortcode content.
     *
     * @return string
     */
    public function render_shortcode( $attributes, $content ) {
        // Raw attributes are captured.
        $raw_attributes = is_array( $attributes ) ? $attributes : [];

        // Default attributes.
        $defaults = [
            'type'      => '',
            'name'      => '',
            'id'        => '',
            'before'    => '',
            'after'     => '',
            'fallback'  => '',
            'format'    => '',
            'delimiter' => '',
        ];

        $attributes = shortcode_atts( $defaults, $attributes, 'anys' );

        // Unknown keys are merged into normalized attributes.
        $attributes = $this->merge_unknown_attributes( $attributes, $raw_attributes, $defaults );

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
        if ( empty( $attributes['type'] ) || empty( $attributes['name'] ) ) {
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

        ob_start();

        // Loads the matching handler file if it exists.
        $file = ANYS_INCLUDES_PATH . "types/anys/{$attributes['type']}.php";

        if ( file_exists( $file ) ) {
            require $file;
        } else {
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
        }

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

        // Returns output if the shortcode type is 'loop'.
        if($attributes['type'] == 'loop'){
            return $output;
        }

        return $output . do_shortcode( $content );
    }

    /**
	 * Merge unknown (non-default) attributes back after shortcode_atts().
	 *
	 * Keeps keys like post_type, s, tax_query, meta_query, etc.
	 *
	 * @param array<string,mixed> $normalized Attributes with defaults applied.
	 * @param array<string,mixed> $raw        Raw attributes from WP.
	 * @param array<string,mixed> $defaults   Default attribute map.
     *
	 * @return array<string,mixed>
	 */
	private function merge_unknown_attributes( array $normalized, array $raw, array $defaults ) : array {
		// Finds non-default keys and appends them.
        $extra = array_diff_key( $raw, $defaults );

        // Keeps normalized values, adds extras.
        return $normalized + $extra;
	}
}

/**
 * Initializes the class.
 *
 * @since 1.0.0
 */
Register_Shortcodes::get_instance();
