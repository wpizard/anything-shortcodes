<?php

namespace AnyS\Modules;

use AnyS\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * AJAX module.
 *
 * Handles central AJAX requests.
 *
 * @since NEXT
 */
final class Ajax {
    use Singleton;

    /**
     * Adds AJAX hooks.
     *
     * @since NEXT
     */
    protected function add_hooks() {
        add_action( 'wp_ajax_anys', [ $this, 'handle_request' ] );
        add_action( 'wp_ajax_nopriv_anys', [ $this, 'handle_request' ] );
    }

    /**
     * Handles central AJAX requests.
     *
     * @since NEXT
     */
    public function handle_request() {
        // Verifies the nonce.
        check_ajax_referer( 'anys_nonce' );

        // Sanitizes incoming data.
        $module  = sanitize_text_field( $_REQUEST['module'] ?? '' );
        $method  = sanitize_text_field( $_REQUEST['method'] ?? '' );
        $payload = $_REQUEST['payload'] ?? [];

        // Checks for missing parameters.
        if (
            empty( $module )
            || empty( $method )
        ) {
            wp_send_json_error( esc_html__( 'Missing module or method.', 'anys' ) );
        }

        // Builds class name dynamically. Example: AnyS\Modules\Templates.
        $class = '\\AnyS\\Modules\\' . ucfirst( $module );

        // Checks if class exists.
        if ( ! class_exists( $class ) ) {
            wp_send_json_error(
                sprintf(
                    /* translators: %s: module name */
                    esc_html__( 'Unknown module: %s', 'anys' ),
                    esc_html( $module )
                )
            );
        }

        // Gets module instance.
        $handler = $class::get_instance();

        // Verifies that the handler supports AJAX.
        if ( ! method_exists( $handler, 'handle_ajax' ) ) {
            wp_send_json_error(
                sprintf(
                    /* translators: %s: module name */
                    esc_html__( 'Module %s does not support AJAX.', 'anys' ),
                    esc_html( $module )
                )
            );
        }

        // Delegates to the module's AJAX handler.
        $handler->handle_ajax( $method, $payload );
    }

}

/**
 * Initializes the module.
 *
 * @since NEXT
 */
Ajax::get_instance();
