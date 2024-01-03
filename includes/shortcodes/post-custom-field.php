<?php

defined( 'ABSPATH' ) or die();

/**
 * Post Custom Field shortcode output.
 *
 * @since 1.0.0
 */

$attributes = anys_get_shortcode_attributes( $attributes );
$value      = get_post_meta( (int) $attributes['post_id'], $attributes['field'], true );
$output     = anys_get_shortcode_output( $attributes, $value );

echo empty( $output ) ? wp_kses_post( $value ) : wp_kses_post( $output );
