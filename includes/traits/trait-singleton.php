<?php

namespace AnyS\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Singleton trait.
 *
 * Use this trait to create singleton classes.
 *
 * @since NEXT
 */
trait Singleton {

    /**
     * The instance.
     *
     * @since NEXT
     *
     * @var static
     */
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
        $this->load_helpers();
        $this->add_hooks();
    }

    /**
     * Adds hooks.
     *
     * @since NEXT
     */
    protected function add_hooks() {}

    /**
     * Loads helpers file for the module if it exists.
     *
     * @since NEXT
     */
    protected function load_helpers() {
        // Gets the module's directory (where the class file is located).
        $reflector    = new \ReflectionClass( $this );
        $dir          = dirname( $reflector->getFileName() );
        $helpers_file = $dir . '/helpers.php';

        if ( file_exists( $helpers_file ) ) {
            require_once $helpers_file;
        }
    }
}
