<?php

namespace AnyS\Modules\Elementor;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;
use Elementor\Core\DynamicTags\Manager as Tags_Manager;

/**
 * Integrates Anything Shortcodes with Elementor.
 *
 * Registers a custom dynamic tags group and the `[anys]` shortcode tag.
 *
 * @since NEXT
 */
final class Elementor {
    use Singleton;

    /**
     * Initializes Elementor hooks.
     *
     * @return void
     */
    protected function add_hooks() : void {
        add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tags' ] );
    }

    /**
     * Registers the custom dynamic tag group and tag.
     *
     * @param Tags_Manager $dynamic_tags Elementor dynamic tags manager.
     * 
     * @return void
     */
    public function register_dynamic_tags( Tags_Manager $dynamic_tags ) : void {
        $dynamic_tags->register_group(
            'anything-shortcodes',
            [ 'title' => esc_html__( 'Anything Shortcodes', 'anys' ) ]
        );

        require_once __DIR__ . '/shortcode-tag.php';

        $dynamic_tags->register( new Shortcode_Tag() );
    }

    /**
     * Bootstraps the module instance.
     *
     * @return void
     */
    public static function init() : void {
        self::get_instance();
    }
}

// Initialize the integration.
Elementor::init();
