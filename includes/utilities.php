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
 * @return string Returns text with prefix.
 */
function anys_prefix( $text ) {
    return ANYS_SLUG . $text;
}

/**
 * Safely calls a PHP function if whitelisted.
 *
 * @param string $function_name
 * @param array  $args
 *
 * @return mixed|null
 */
function anys_call_function( $function_name, $args = [] ) {
    $whitelisted_functions = anys_get_whitelisted_functions();

    if ( function_exists( $function_name ) && in_array( $function_name, $whitelisted_functions, true ) ) {
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
 * - {shortcode:(tag)}
 * - {const:NAME}
 *
 * @param string|array $value Attribute value or array of values.
 * @param array        $cache Internal cache (used recursively).
 *
 * @return string|array
 */
function anys_parse_dynamic_value( $value, &$cache = [] ) {
    if ( is_array( $value ) ) {
        foreach ( $value as $k => $v ) {
            $value[ $k ] = anys_parse_dynamic_value( $v, $cache );
        }

        return $value;
    }

    if ( ! is_string( $value ) ) {
        return $value;
    }

    if ( isset( $cache[ $value ] ) ) {
        return $cache[ $value ];
    }

    $whitelisted_functions = anys_get_whitelisted_functions();

    $callback = function( $full ) use ( &$whitelisted_functions, &$cache ) {
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

            if ( ! in_array( $function, $whitelisted_functions, true ) ) {
                return '';
            }

            $args = isset( $m[2] ) ? array_map( 'trim', explode( ',', $m[2] ) ) : [];
            $args = array_map( function( $arg ) use ( &$whitelisted_functions, &$cache ) {
                return anys_parse_dynamic_value( $arg, $cache );
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

        if ( preg_match( '/^\{shortcode:\((.*)\)\}$/', $full, $m ) ) {
            $val = do_shortcode( '[' . $m[1] . ']' );
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
 *
 * @return array
 */
function anys_parse_dynamic_attributes( $atts ) {
    $cache = [];

    foreach ( $atts as $key => $value ) {
        $atts[ $key ] = anys_parse_dynamic_value( $value, $cache );
    }

    return $atts;
}

/**
 * Gets the whitelisted functions list.
 *
 * @since 1.1.0
 *
 * @return array The list of whitelisted function names.
 */
function anys_get_whitelisted_functions() {
    // Default whitelisted functions.
    $default_functions = anys_get_default_whitelisted_functions();

    // Get user-defined functions from settings.
    $options        = get_option( 'anys_settings' );
    $user_functions = isset( $options['anys_function_whitelist'] ) && is_array( $options['anys_function_whitelist'] )
        ? $options['anys_function_whitelist']
        : [];

    // Merge and remove duplicates.
    $all_functions = array_unique( array_merge( $default_functions, $user_functions ) );

    // Keep only existing functions.
    $whitelisted_functions = array_filter( $all_functions, 'function_exists' );

    return $whitelisted_functions;
}

/**
 * Gets the default whitelisted functions list.
 *
 * @since 1.1.0
 *
 * @return array The list of default whitelisted function names.
 */
function anys_get_default_whitelisted_functions() {
    $default_functions = [
        'abs',
        'ceil',
        'floor',
        'round',
        'strtoupper',
        'strtolower',
        'ucfirst',
        'lcfirst',
        'ucwords',
        'strlen',
        'strpos',
        'substr',
        'trim',
        'nl2br',
        'htmlspecialchars',
        'htmlentities',
        'urlencode',
        'urldecode',
        'json_encode',
        'json_decode',
        'implode',
        'explode',
        'count',
        'number_format',
        'date',
        'time',
        'esc_html',
        'esc_html_e',
        'esc_attr',
        'esc_url',
        'esc_textarea',
        'wp_kses_post',
        'wp_kses_data',
        'sanitize_text_field',
        'sanitize_email',
        'sanitize_key',
        'sanitize_html_class',
        'sanitize_title',
        'sanitize_user',
        'wp_trim_words',
        'wp_strip_all_tags',
        'wp_specialchars_decode',
        'wpautop',
        'date_i18n',
        'wp_date',
        'current_time',
        'get_locale',
        'anys_date_i18n_jalali',
    ];

    return apply_filters( 'anys/default_whitelisted_functions', $default_functions );
}

/**
 * Forces or overrides a single shortcode attribute.
 *
 * @since NEXT
 *
 * @param string $shortcode Single-tag shortcode (e.g. "[anys ...]").
 * @param string $attr      Attribute name.
 * @param string $value     Attribute value.
 *
 * @return string Modified shortcode string.
 */
function anys_force_shortcode_attr( $shortcode, $attr, $value ) {
    $shortcode = (string) $shortcode;

    // Replaces existing attribute.
    $pattern_replace = '/(\s' . preg_quote( $attr, '/' ) . '\s*=\s*)(["\'])(.*?)\2/i';

    if ( preg_match( $pattern_replace, $shortcode ) ) {
        return preg_replace( $pattern_replace, '$1"' . addslashes( $value ) . '"', $shortcode, 1 );
    }

    // Adds attribute if missing.
    if ( preg_match( '/^\[[a-zA-Z0-9_-]+(?:\s+[^]]*?)?\]$/', $shortcode ) ) {
        return substr( $shortcode, 0, -1 ) . ' ' . $attr . '="' . esc_attr( $value ) . '"]';
    }

    return $shortcode;
}

/**
 * Resolve final callable and args before dispatching the function call.
 *
 * - Keeps backward compatibility.
 * - Allows feature flags (e.g., calendar="jalali") to map to internal closures safely.
 *
 * @param string $function   Requested function name.
 * @param array  $args       Requested args (already parsed).
 * @param array  $attributes Shortcode attributes (parsed).
 *
 * @return array [callable|string, array, array] Callable, args, modified attributes
 *
 * @since NEXT
 */
function anys_resolve_function_call( $function, array $args, array $attributes ) {
    $target_function = (string) $function;
    $format = isset( $attributes['format'] ) ? strtolower( trim( (string) $attributes['format'] ) ) : '';

    // Returns early if not Jalali format.
    if ( $format === '' || stripos( $format, 'jalali' ) !== 0 ) {
        return [ $target_function, $args, $attributes ];
    }

    // Handles Jalali only if function is date_i18n and Jalali library exists.
    if ( strtolower( $target_function ) !== 'date_i18n' || ! class_exists( '\Morilog\Jalali\Jalalian' ) ) {
        return [ $target_function, $args, $attributes ];
    }

    // Determines the Jalali format pattern.
    if ( $format === 'jalali' || $format === 'jalali_date' ) {
        $pattern = (string) get_option( 'date_format' );
    } elseif ( $format === 'jalali_datetime' ) {
        $pattern = (string) ( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
    } else {
        // Returns original values if format is not recognized.
        return [ $target_function, $args, $attributes ];
    }

    // Extracts timestamp argument or uses current time.
    $timestamp = isset( $args[1] ) && is_numeric( $args[1] )
        ? (int) $args[1]
        : (int) current_time( 'timestamp' );

    // Builds callable closure to replace date_i18n with Jalali formatting.
    $callable = static function( $fmt, $ts ) {
        $tz = wp_timezone();
        $dt = ( new DateTimeImmutable( '@' . (int) $ts ) )->setTimezone( $tz );
        return \Morilog\Jalali\Jalalian::fromDateTime( $dt )->format( (string) $fmt );
    };

    // Prepares final callable arguments.
    $final_args = [ $pattern, $timestamp ];

    return [ $callable, $final_args, $attributes ];
}

/**
 * Formats a date or timestamp by the selected calendar.
 *
 * @since NEXT
 * @deprecated Use anys_date_i18n_jalali() instead.
 *
 * @param string $pattern   Format pattern.
 * @param mixed  $value     Timestamp, string, or DateTime.
 * @param string $calendar  Calendar type ('gregorian' or 'jalali').
 *
 * @return string Formatted date string.
 */
function anys_date_i18n( $pattern, $value = null, $calendar = 'gregorian' ) {
    // Calendar name is normalized.
    $calendar = strtolower( trim( (string) $calendar ) );

    // Timestamp is extracted from input.
    $to_timestamp = static function( $val ) {
        if ( $val instanceof \DateTimeInterface ) {
            return (int) $val->getTimestamp();
        }
        if ( is_numeric( $val ) ) {
            return (int) $val;
        }
        if ( is_string( $val ) ) {
            $time = strtotime( $val );
            if ( $time !== false ) {
                return (int) $time;
            }
        }
        // Current time is returned if parsing fails.
        return (int) current_time( 'timestamp' );
    };

    // Timestamp is resolved.
    $timestamp = $to_timestamp( $value );

    // Jalali date is used if library exists.
    if ( $calendar === 'jalali' && class_exists( '\Morilog\Jalali\Jalalian' ) ) {
        $timezone = wp_timezone();
        $datetime = ( new \DateTimeImmutable( '@' . $timestamp ) )->setTimezone( $timezone );
        return \Morilog\Jalali\Jalalian::fromDateTime( $datetime )->format( (string) $pattern );
    }

    // Gregorian date is returned as fallback.
    return date_i18n( (string) $pattern, $timestamp );
}
/**
 * Checks if content has any shortcode.
 *
 * @since NEXT
 *
 * @param string $content The content to check.
 *
 * @return bool True if any shortcode exists, false otherwise.
 */
function anys_has_shortcode( $content ) {
    if ( preg_match( '/\[[a-zA-Z0-9_]+[^\]]*\]/', $content ) ) {
        return true;
    }

    return false;
}
/**
 * Jalali formatter via Morilog\Jalali; falls back to date_i18n().
 *
 * @since NEXT
 *
 * @param string    $format
 * @param int|false $timestamp
 * @param bool      $gmt
 *
 * @return string
 */
function anys_date_i18n_jalali( $format, $timestamp = false, $gmt = false ) {
	// Timestamp is resolved like core.
	$resolved_timestamp = ( false === $timestamp )
		? (int) current_time( 'timestamp', $gmt )
		: (int) $timestamp;

	// Availability is cached per request.
	static $jalali_available = null;
	if ( $jalali_available === null ) {
		$jalali_available = class_exists( '\Morilog\Jalali\Jalalian' );
	}

	// Core is delegated to when Morilog is absent.
	if ( ! $jalali_available ) {
		return date_i18n( (string) $format, $resolved_timestamp, $gmt );
	}

	// Site timezone is cached (UTC when $gmt is true).
	static $cached_site_timezone = null;
	$timezone_object = $gmt ? new \DateTimeZone( 'UTC' )
		: ( $cached_site_timezone ?: ( $cached_site_timezone = wp_timezone() ) );

	// Zoned DateTime is constructed.
	$datetime_object = ( new \DateTimeImmutable( '@' . $resolved_timestamp ) )
		->setTimezone( $timezone_object );

	// Jalali output is produced.
	$formatted_output = \Morilog\Jalali\Jalalian::fromDateTime( $datetime_object )
		->format( (string) $format );

	// Core filter is applied for consistency.
	return apply_filters( 'date_i18n', $formatted_output, (string) $format, $resolved_timestamp, $gmt );
}
