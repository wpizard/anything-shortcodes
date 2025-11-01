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
 * Splits content by "[anys else]" marker.
 *
 * @since NEXT
 *
 * @param string $content
 *
 * @return array{item:string, else:string}
 */
function anys_split_else_block( string $content ): array {
	$raw   = shortcode_unautop( $content );
	$parts = preg_split( '/\[\s*anys\s+else\s*\]/i', $raw, 2 );

	return [
		'item' => isset( $parts[0] ) ? trim( $parts[0] ) : '',
		'else' => isset( $parts[1] ) ? trim( $parts[1] ) : '',
	];
}

/**
 * Builds sanitized WP_Query args from shortcode attributes.
 *
 * @since NEXT
 *
 * @param array<string,mixed> $atts
 *
 * @return array<string,mixed>
 */
function anys_build_wp_query_args( array $atts ): array {
	$args = [];

	foreach ( [
		'post_type','posts_per_page','orderby','order',
		'author','paged','offset','post_status',
		'meta_key','meta_value','meta_compare',
	] as $key ) {
		if ( isset( $atts[ $key ] ) && $atts[ $key ] !== '' ) {
			$args[ $key ] = sanitize_text_field( (string) $atts[ $key ] );
		}
	}

	foreach ( [ 'posts_per_page', 'author', 'paged', 'offset' ] as $n ) {
		if ( isset( $args[ $n ] ) ) {
			$args[ $n ] = (int) $args[ $n ];
		}
	}

	// Passes 's' as-is (WP_Query sanitizes internally).
	if ( isset( $atts['s'] ) && $atts['s'] !== '' ) {
		$args['s'] = (string) wp_unslash( $atts['s'] );
	}

	// tax_query (JSON)
	if ( ! empty( $atts['tax_query'] ) ) {
		$decoded = json_decode( (string) wp_unslash( $atts['tax_query'] ), true );
		if ( is_array( $decoded ) ) {
			$clean = [];
			foreach ( $decoded as $row ) {
				if ( ! is_array( $row ) ) continue;
				$item = [];
				if ( ! empty( $row['taxonomy'] ) ) $item['taxonomy'] = sanitize_key( $row['taxonomy'] );
				$item['field'] = ( isset( $row['field'] ) && in_array( $row['field'], [ 'term_id', 'name', 'slug' ], true ) )
					? $row['field'] : 'slug';
				if ( isset( $row['terms'] ) ) {
					$item['terms'] = is_array( $row['terms'] )
						? array_map( 'sanitize_text_field', $row['terms'] )
						: [ sanitize_text_field( (string) $row['terms'] ) ];
				}
				if ( isset( $row['operator'] ) && in_array( $row['operator'], [ 'IN', 'NOT IN', 'AND', 'EXISTS', 'NOT EXISTS' ], true ) ) {
					$item['operator'] = $row['operator'];
				}
				if ( $item ) $clean[] = $item;
			}
			if ( $clean ) $args['tax_query'] = $clean;
		}
	}

	// meta_query (JSON)
	if ( ! empty( $atts['meta_query'] ) ) {
		$decoded = json_decode( (string) wp_unslash( $atts['meta_query'] ), true );
		if ( is_array( $decoded ) ) {
			$clean = [];
			foreach ( $decoded as $row ) {
				if ( ! is_array( $row ) ) continue;
				$item = [];
				if ( isset( $row['key'] ) )    $item['key'] = sanitize_key( $row['key'] );
				if ( isset( $row['value'] ) )  $item['value'] = is_array( $row['value'] )
					? array_map( 'sanitize_text_field', $row['value'] )
					: sanitize_text_field( (string) $row['value'] );
				if ( isset( $row['compare'] ) ) $item['compare'] = strtoupper( (string) $row['compare'] );
				if ( isset( $row['type'] ) )    $item['type'] = strtoupper( (string) $row['type'] );
				if ( $item ) $clean[] = $item;
			}
			if ( $clean ) $args['meta_query'] = $clean;
		}
	}

	if ( empty( $args['post_type'] ) ) {
		$args['post_type'] = 'post';
	}
	if ( empty( $args['posts_per_page'] ) || (int) $args['posts_per_page'] <= 0 ) {
		$args['posts_per_page'] = 10;
	}

	// Skips total row count for speed.
	$args['no_found_rows'] = true;
	// Ignores sticky posts.
	$args['ignore_sticky_posts'] = true;
	// Suppresses external filters.
	$args['suppress_filters'] = true;

	return $args;
}

/**
 * Maps 'search_in' to 'search_columns'.
 *
 * @since NEXT
 *
 * @param array<string,mixed> $query_args
 * @param array<string,mixed> $atts
 *
 * @return array<string,mixed>
 */
function anys_apply_search_columns( array $query_args, array $atts ): array {
	if ( empty( $query_args['s'] ) ) {
		return $query_args;
	}

	$mode = isset( $atts['search_in'] ) ? strtolower( trim( (string) $atts['search_in'] ) ) : 'all';

	if ( $mode === 'title' ) {
		$query_args['search_columns'] = [ 'post_title' ];
	} elseif ( $mode === 'title_excerpt' || $mode === 'excerpt_title' ) {
		$query_args['search_columns'] = [ 'post_title', 'post_excerpt' ];
	}

	return $query_args;
}

/**
 * Detects container post ID safely.
 *
 * @since NEXT
 *
 * @return int
 */
function anys_detect_container_post_id(): int {
	global $post;
	if ( $post instanceof \WP_Post ) {
		return (int) $post->ID;
	}

	$queried_object = get_queried_object();
	return ( $queried_object instanceof \WP_Post ) ? (int) $queried_object->ID : 0;
}

/**
 * Determines if the container post should be excluded.
 *
 * @since NEXT
 *
 * @param array<string,mixed> $query_args
 * @param array<string,mixed> $atts
 * @param int                 $container_id
 *
 * @return bool
 */
function anys_should_exclude_container( array $query_args, array $atts, int $container_id ): bool {
	if ( $container_id <= 0 || empty( $query_args['s'] ) ) {
		return false;
	}

	$policy = isset( $atts['search_in'] ) ? strtolower( trim( (string) $atts['search_in'] ) ) : 'all';
	$hay    = '';

	if ( in_array( $policy, [ 'title', 'title_excerpt', 'excerpt_title', 'all' ], true ) ) {
		$hay .= ' ' . get_the_title( $container_id );
	}
	if ( in_array( $policy, [ 'title_excerpt', 'excerpt_title', 'all' ], true ) ) {
		$hay .= ' ' . get_the_excerpt( $container_id );
	}
	if ( $policy === 'all' ) {
		$hay .= ' ' . get_post_field( 'post_content', $container_id );
	}

	$hay = wp_strip_all_tags( strip_shortcodes( (string) $hay ) );
	return ( $hay !== '' && stripos( $hay, (string) $query_args['s'] ) !== false );
}

/**
 * Renders a template with minimal overhead.
 *
 * @since NEXT
 *
 * @param string $template
 *
 * @return string
 */
function anys_render_template_fast( string $template ): string {
	return ( function_exists( 'anys_has_shortcode' ) && anys_has_shortcode( $template ) )
		? do_shortcode( $template )
		: $template;
}
