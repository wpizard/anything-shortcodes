<?php

namespace AnyS\Elementor;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Core\DynamicTags\Manager as Tags_Manager;

/**
 * Elementor integration for Anything Shortcodes.
 *
 * @since NEXT
 */
final class Elementor {

    /**
     * Instance.
     *
     * @since NEXT
     *
     * @var Elementor|null
     */
    private static $instance = null;

    /**
     * Gets instance.
     *
     * @since NEXT
     *
     * @return Elementor
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since NEXT
     */
    private function __construct() {
        add_action( 'elementor/init', [ $this, 'add_hooks' ] );
    }

    /**
     * Adds hooks.
     *
     * @since NEXT
     *
     * @return void
     */
    public function add_hooks() {
        add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tags' ] );
    }

    /**
     * Registers group and tag.
     *
     * @since NEXT
     *
     * @param Tags_Manager $dynamic_tags Elementor manager.
     *
     * @return void
     */
    public function register_dynamic_tags( Tags_Manager $dynamic_tags ) {
        $dynamic_tags->register_group(
            'anything-shortcodes',
            [ 'title' => esc_html__( 'Anything Shortcodes', 'anys' ) ]
        );

        require_once __DIR__ . '/shortcode-tag.php';

        $dynamic_tags->register( new Tags\Shortcode_Tag() );
    }
}

Elementor::get_instance();
