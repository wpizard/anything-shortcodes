<?php

defined( 'ABSPATH' ) or die();

use \Anything_Shortcodes\Utilities;

/**
 * Post Custom Field shortcode output.
 *
 * @since 1.0.0
 */

$attributes = Utilities::get_attributes( $attributes );
$value      = get_post_meta( (int) $attributes['post_id'], $attributes['field'], true );
$output     = Utilities::get_output( $attributes, $value );

echo empty( $output ) ? $value : $output;
