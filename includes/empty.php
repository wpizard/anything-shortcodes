<?php

namespace RAC_0;

defined( 'ABSPATH' ) or die();

/**
 * Empty class.
 *
 * @since 1.0.0
 */
final class Empty {

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
     * @return Empty
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

    }
}

/**
 * Initializes the class.
 *
 * @since 1.0.0
 */
Empty::get_instance();
