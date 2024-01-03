<?php

defined( 'ABSPATH' ) or die();

/**
 * Post Field shortcode output.
 *
 * @since 1.0.0
 */

$attributes = anys_get_shortcode_attributes( $attributes );
$value      = get_post_field( $attributes['field'], $attributes['post_id'] );
$output     = anys_get_shortcode_output( $attributes, $value );

echo empty( $output ) ? wp_kses_post( $value ) : wp_kses_post( $output );
