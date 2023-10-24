<?php

defined( 'ABSPATH' ) or die();

use \Anything_Shortcodes\Utilities;

/**
 * Post Field shortcode output.
 *
 * @since 1.0.0
 */

$attributes = Utilities::get_attributes( $attributes );
$value      = get_post_field( $attributes['field'], $attributes['post_id'] );
$output     = Utilities::get_output( $attributes, $value );

echo empty( $output ) ? $value : $output;
