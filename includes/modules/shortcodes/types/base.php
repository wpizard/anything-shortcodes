<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || die();

/**
 * Base shortcode type class.
 *
 * Provides common functionality for all shortcode types.
 *
 * @since NEXT
 */
abstract class Base {

    /**
     * Returns the shortcode type.
     *
     * @since NEXT
     *
     * @return string
     */
    abstract public function get_type();

    /**
     * Returns the default shortcode attributes.
     *
     * @since NEXT
     *
     * @return array
     */
    protected function get_defaults() {
        return [];
    }

    /**
     * Normalizes attributes with defaults.
     *
     * @since NEXT
     *
     * @param array $attributes Raw attributes.
     *
     * @return array Merged attributes.
     */
    protected function get_attributes( array $attributes ) {
        $defaults = $this->get_defaults();

        $attributes = wp_parse_args(
            $attributes,
            $defaults,
        );

        return $attributes;
    }

    /**
     * Renders the shortcode output.
     *
     * @since NEXT
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Enclosed content.
     *
     * @return string
     */
    abstract public function render( array $attributes, string $content );
}
