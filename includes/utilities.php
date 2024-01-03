<?php
/**
 * Utilities.
 *
 * @since 1.0.0
 */

/**
 * Gets value from $_GET or defined $haystack.
 *
 * @since 1.0.0
 *
 * @param string $needle   Name of the searched key.
 * @param mixed  $haystack Optional. The target to search. If false, $_GET is set to be the $haystack.
 * @param mixed  $default  Optional. Value to return if the needle isn't found.
 *
 * @return string Returns if the value is found; else $default is returned.
 */
function anys_get( $needle, $haystack = false, $default = null ) {

    if ( false === $haystack ) {
        $haystack = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- The nonce verification check should be at the form processing level.
    }

    $haystack = (array) $haystack;

    if ( isset( $haystack[ $needle ] ) ) {
        return $haystack[ $needle ];
    }

    return $default;
}

/**
 * Get value from $_POST.
 *
 * @since 1.0.0
 *
 * @param string $needle  Name of the searched key.
 * @param mixed  $default Optional. Value to return if the needle isn't found.
 *
 * @return string Returns the value if found; else $default is returned.
 */
function anys_post( $needle, $haystack = false, $default = null ) {

    if ( false === $haystack ) {
        $haystack = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- The nonce verification check should be at the form processing level.
    }

    return anys_get( $needle, $haystack, $default ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- The nonce verification check should be at the form processing level.
}

/**
 * Get value from $_GET or $_POST superglobals.
 *
 * @since 1.0.0
 *
 * @param string $needle  Name of the searched key.
 * @param mixed  $default Optional. Value to return if the needle isn't found.
 *
 * @return string Returns the value if found; else $default is returned.
 */
function anys_get_or_post( $needle, $default = null ) {
    $get = anys_get( $needle );

    if ( $get ) {
        return $get;
    }

    $post = anys_post( $needle );

    if ( $post ) {
        return $post;
    }

    return $default;
}

/**
 * Adds plugin prefix to a text.
 *
 * @since 1.0.0
 *
 * @return boolean Returns text with prefix.
 */
function anys_prefix( $text ) {
    return ANYS_SLUG . $text;
}

/**
 * Get attributes.
 *
 * @since 1.0.0
 *
 * @param array $attributes  Name of the searched key.
 *
 * @return array Returns the value if found; else $default is returned.
 */
function anys_get_shortcode_attributes( $attributes ) {
    $attributes            = array_map( 'trim', $attributes );
    $attributes['post_id'] = empty( $attributes['post_id'] ) ? get_the_ID() : $attributes['post_id'];

    foreach ( $attributes as $attribute_key => $attribute ) {
        if ( 'output' === $attribute_key ) {
            continue;
        }

        // Shortcode.
        if ( str_contains( $attribute, 'sc:' ) ) {
            $attributes[ $attribute_key ] = anys_do_shortcode_attribute_shortcode( $attributes, $attribute_key, $attribute );
            continue;
        }

        // Function.
        if ( str_contains( $attribute, 'fn:' ) ) {
            $attributes[ $attribute_key ] = anys_call_shortcode_attribute_function( $attributes, $attribute_key, $attribute );
            continue;
        }
    }

    return $attributes;
}

/**
 * Get Output.
 *
 * @since 1.0.0
 */
function anys_get_shortcode_output( $attributes, $value ) {
    if ( empty( $attributes['output'] ) ) {
        return;
    }

    $attributes['value'] = $value;
    $output              = '';

    // HTML.
    if ( str_contains( $attributes['output'], 'html:' ) ) {
        $attributes['output'] = str_replace( 'html:', '', $attributes['output'] );
        $attributes['output'] = ltrim( $attributes['output'], "</p>" );
        $attributes['output'] = rtrim( $attributes['output'], "<p>" );

        $output = str_replace( '$value', $value, $attributes['output'] );
    }

    // Shortcode.
    if ( str_contains( $attributes['output'], 'sc:' ) ) {
        $output = anys_do_shortcode_attribute_shortcode( $attributes, 'output', $attributes['output'] );
    }

    // Function.
    if ( str_contains( $attributes['output'], 'fn:' ) ) {
        $output = anys_call_shortcode_attribute_function( $attributes, 'output', $attributes['output'] );
    }

    return $output;
}

/**
 * Call shortcode in attributes/output.
 *
 * @since 1.0.0
 */
function anys_do_shortcode_attribute_shortcode( $attributes, $attribute_key, $attribute ) {
    $shortcode            = str_replace( 'sc:', '', $attribute );
    $shortcode_attributes = '';

    /**
     * Filters the content of the a shortcode.
     *
     * The dynamic portion of the hook name, `$attributes['name']`, refers to
     * the shortcode name.
     *
     * Possible hook names include:
     *
     *  - `anys/shortcodes/post-field/content`
     *  - `anys/shortcodes/post-custom-fields/content`
     *
     * @since 1.0.0
     *
     * @param string $content    Shortcode content.
     * @param array  $attributes Shortcode attributes.
     */
    $attributes = apply_filters(
        "anys/shortcodes/{$attributes['name']}/attributes/{$attribute_key}/shortcode",
        $attributes
    );

    foreach ( $attributes as $key => $value ) {
        $shortcode_attributes .= "$key='$value' ";
    }

    return do_shortcode( "[{$shortcode} $shortcode_attributes]" );
}

/**
 * Call function in attributes/output.
 *
 * @since 1.0.0
 */
function anys_call_shortcode_attribute_function( $attributes, $attribute_key, $attribute ) {
    $function = str_replace( 'fn:', '', $attribute );

    if ( ! is_callable( $function ) ) {
        return;
    }

    /**
     * Filters the content of the a shortcode.
     *
     * The dynamic portion of the hook name, `$attributes['name']`, refers to
     * the shortcode name.
     *
     * Possible hook names include:
     *
     *  - `anys/shortcodes/post-field/content`
     *  - `anys/shortcodes/post-custom-fields/content`
     *
     * @since 1.0.0
     *
     * @param string $content    Shortcode content.
     * @param array  $attributes Shortcode attributes.
     */
    $function_attributes = apply_filters(
        "anys/shortcodes/{$attributes['name']}/attributes/{$attribute_key}/function",
        $attributes
    );

    return call_user_func_array( $function, [ $function_attributes ] );
}
