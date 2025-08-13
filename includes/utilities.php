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
 * Safely calls a PHP function if allowed.
 *
 * @param string $function_name
 * @param array  $args
 * @param array  $allowed_functions Whitelisted functions.
 *
 * @return mixed|null
 */
function anys_call_function( $function_name, $args = [], $allowed_functions = [] ) {
    if ( empty( $allowed_functions ) ) {
        $allowed_functions = [
            'get_the_ID',
            'intval',
            'sanitize_text_field',
            'wp_get_current_user',
            'get_post_status',
            // Add other allowed functions here
        ];
    }

    if ( function_exists( $function_name ) && in_array( $function_name, $allowed_functions, true ) ) {
        return call_user_func_array( $function_name, $args );
    }

    return null;
}

/**
 * Formats a value based on given format.
 *
 * @since 1.0.0
 * @since 1.1.0 Add more formats.
 *
 * @param mixed  $value
 * @param string $format
 * @return string
 */
function anys_format_value( $value, $attributes = [] ) {
    if ( empty( $attributes['format'] ) ) {
        return $value;
    }

    $format    = $attributes['format'];
    $delimiter = isset( $attributes['delimiter'] ) ? $attributes['delimiter'] : ', ';

    switch ( $format ) {
        case 'date':
            return date_i18n( get_option( 'date_format' ), strtotime( $value ) );

        case 'datetime':
            return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $value ) );

        case 'number':
            return number_format_i18n( floatval( $value ) );

        case 'json':
            return wp_json_encode( $value );

        case 'serialize':
            return maybe_serialize( $value );

        case 'unserialize':
            return maybe_unserialize( $value );

        case 'print_r':
            return print_r( $value, true );

        case 'var_export':
            return var_export( $value, true );

        case 'implode':
            if ( is_array( $value ) ) {
                if ( array_values( $value ) !== $value ) {
                    return implode( $delimiter, array_values( $value ) );
                }
                return implode( $delimiter, $value );
            }

            return $value;

        case 'keys':
            if ( is_array( $value ) ) {
                return implode( $delimiter, array_keys( $value ) );
            }

            return $value;

        case 'capitalize':
            if ( is_string( $value ) ) {
                return mb_convert_case( $value, MB_CASE_TITLE, "UTF-8" );
            }

            return $value;

        case 'uppercase':
            if ( is_string( $value ) ) {
                return mb_strtoupper( $value, "UTF-8" );
            }

            return $value;

        case 'lowercase':
            if ( is_string( $value ) ) {
                return mb_strtolower( $value, "UTF-8" );
            }

            return $value;

        case 'strip_tags':
            if ( is_string( $value ) ) {
                return strip_tags( $value );
            }

            return $value;

        case 'values':
            if ( is_array( $value ) ) {
                return implode( $delimiter, array_values( $value ) );
            }

            return $value;

        case 'keys_values':
            if ( is_array( $value ) ) {
                $pairs = [];
                foreach ( $value as $k => $v ) {
                    $pairs[] = "{$k}: {$v}";
                }

                return implode( $delimiter, $pairs );
            }

            return $value;

        default:
            /**
             * Filter for custom formats.
             *
             * @param mixed  $value
             * @param string $format
             */
            return apply_filters( "anys/format/{$format}", $value, $format );
    }
}

/**
 * Wraps output with before/after strings and applies fallback.
 *
 * @param mixed $value
 * @param array $attributes
 *
 * @return string
 */
function anys_wrap_output( $value, $attributes = [] ) {
    if ( empty( $value ) && ! empty( $attributes['fallback'] ) ) {
        $value = $attributes['fallback'];
    }

    $before = $attributes['before'] ?? '';
    $after  = $attributes['after'] ?? '';

    return $before . $value . $after;
}

/**
 * Recursively parses dynamic attribute values and replaces placeholders with caching and security.
 *
 * Supported placeholders:
 * - {get:param}
 * - {post:param}
 * - {func:function,arg1,arg2}
 * - {shortcode:[tag]}
 * - {const:NAME}
 *
 * @param string|array $value Attribute value or array of values.
 * @param array        $allowed_functions Whitelisted PHP functions allowed to call.
 * @param array        $cache Internal cache (used recursively).
 *
 * @return string|array
 */
function anys_parse_dynamic_value( $value, $allowed_functions = [], &$cache = [] ) {
    if ( is_array( $value ) ) {
        foreach ( $value as $k => $v ) {
            $value[ $k ] = anys_parse_dynamic_value( $v, $allowed_functions, $cache );
        }

        return $value;
    }

    if ( ! is_string( $value ) ) {
        return $value;
    }

    if ( isset( $cache[ $value ] ) ) {
        return $cache[ $value ];
    }

    if ( empty( $allowed_functions ) ) {
        $allowed_functions = [
            'get_the_ID',
            'intval',
            'sanitize_text_field',
            'wp_get_current_user',
            'get_post_status',
        ];
    }

    $callback = function( $full ) use ( &$allowed_functions, &$cache ) {
        if ( preg_match( '/^\{get:([a-zA-Z0-9_-]+)\}$/', $full, $m ) ) {
            $val = isset( $_GET[ $m[1] ] ) ? sanitize_text_field( wp_unslash( $_GET[ $m[1] ] ) ) : '';
            $cache[ $full ] = $val;
            return $val;
        }

        if ( preg_match( '/^\{post:([a-zA-Z0-9_-]+)\}$/', $full, $m ) ) {
            $val = isset( $_POST[ $m[1] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $m[1] ] ) ) : '';
            $cache[ $full ] = $val;
            return $val;
        }

        if ( preg_match( '/^\{func:([a-zA-Z0-9_\\\\]+)(?:,(.*))?\}$/', $full, $m ) ) {
            $function = $m[1];

            if ( ! in_array( $function, $allowed_functions, true ) ) {
                return '';
            }

            $args = isset( $m[2] ) ? array_map( 'trim', explode( ',', $m[2] ) ) : [];
            $args = array_map( function( $arg ) use ( &$allowed_functions, &$cache ) {
                return anys_parse_dynamic_value( $arg, $allowed_functions, $cache );
            }, $args );

            if ( function_exists( $function ) ) {
                $val = call_user_func_array( $function, $args );
                if ( is_string( $val ) ) {
                    $val = sanitize_text_field( $val );
                }
                $cache[ $full ] = $val;
                return $val;
            }

            return '';
        }

        if ( preg_match( '/^\{shortcode:(\[.*\])\}$/', $full, $m ) ) {
            $val = do_shortcode( $m[1] );
            $val = wp_strip_all_tags( $val );
            $cache[ $full ] = $val;
            return $val;
        }

        if ( preg_match( '/^\{const:([A-Z0-9_]+)\}$/', $full, $m ) ) {
            $val = defined( $m[1] ) ? constant( $m[1] ) : '';
            $cache[ $full ] = $val;
            return $val;
        }

        return $full;
    };

    $previous_value = null;
    $current_value = $value;

    while ( $previous_value !== $current_value ) {
        $previous_value = $current_value;

        if ( preg_match( '/^\{(get|post|func|shortcode|const):.*\}$/', $current_value ) ) {
            $current_value = $callback( $current_value );
        } else {
            break;
        }
    }

    $cache[ $value ] = $current_value;

    return $current_value;
}

/**
 * Parses all shortcode attributes recursively with caching and security.
 *
 * @param array $atts
 * @param array $allowed_functions Optional whitelist of PHP functions allowed.
 *
 * @return array
 */
function anys_parse_dynamic_attributes( $atts, $allowed_functions = [] ) {
    $cache = [];

    foreach ( $atts as $key => $value ) {
        $atts[ $key ] = anys_parse_dynamic_value( $value, $allowed_functions, $cache );
    }

    return $atts;
}
