<?php

namespace Anything_Shortcodes;

defined( 'ABSPATH' ) or die();

/**
 * Utilities class.
 *
 * @since 1.0.0
 */
final class Utilities {

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
     * @return Utilities
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Generates a real uniqid.
     *
     * https://www.php.net/manual/en/function.uniqid.php
     *
     * @since 1.0.0
     */
    public static function uniqid( $lenght = 13 ) {
        if ( function_exists( 'random_bytes' ) ) {
            $bytes = random_bytes( ceil( $lenght / 2 ) );
        } elseif ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
            $bytes = openssl_random_pseudo_bytes( ceil( $lenght / 2 ) );
        } else {
            throw new Exception( esc_html__( 'No cryptographically secure random function available.', 'anything-shortcodes' ) );
        }

        return substr( bin2hex( $bytes ), 0, $lenght );
    }

    /**
     * Remove a method for an hook when, it's a class method used and class don't have global for instanciation.
     *
     * https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php
     *
     * @since 1.0.0
     */
    public static function remove_hooks_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
        global $wp_filter;

        // Take only filters on right hook name and priority.
        if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
            return false;
        }

        // Loop on filters registered
        foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
            // Test if filter is an array ! (always for class/method).
            if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
                // Test if object is a class and method is equal to param !
                if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && $filter_array['function'][1] == $method_name ) {
                    // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/).
                    if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
                        unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
                    } else {
                        unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
                    }
                }
            }

        }

        return false;
    }

    /**
     * Remove a method for an hook when, it's a class method used and class don't have variable, but you know the class name.
     *
     * https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php
     *
     * @since 1.0.0
     */
    public static function remove_hooks_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
        global $wp_filter;

        // Take only filters on right hook name and priority.
        if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
            return false;
        }

        // Loop on filters registered
        foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
            // Test if filter is an array ! (always for class/method).
            if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
                // Test if object is a class, class and method is equal to param !
                if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
                    // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/).
                    if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
                        unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
                    } else {
                        unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
                    }
                }
            }

        }

        return false;
    }
}

/**
 * Initializes the class.
 *
 * @since 1.0.0
 */
Utilities::get_instance();
