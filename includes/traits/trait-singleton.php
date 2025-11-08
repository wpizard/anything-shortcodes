<?php

namespace AnyS\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Singleton trait.
 *
 * @since NEXT
 */
trait Singleton {
    private static $instance;

    /**
     * Returns the instance.
     *
     * @since NEXT
     *
     * @return static
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Protected constructor.
     *
     * @since NEXT
     */
    protected function __construct() {
        $this->load_functions();
        $this->add_hooks();
    }

    /**
     * Adds hooks.
     *
     * @since NEXT
     */
    protected function add_hooks() {}

    /**
     * Loads helper file for the module if it exists.
     *
     * @since NEXT
     */
    protected function load_functions() {
        // Gets the module's directory (where the class file is located).
        $reflector      = new \ReflectionClass( $this );
        $dir            = dirname( $reflector->getFileName() );
        $functions_file = $dir . '/functions.php';

        if ( file_exists( $functions_file ) ) {
            require_once $functions_file;
        }
    }
}
