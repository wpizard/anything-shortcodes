<?php
/**
 * Registers Elementor dynamic tags group and the Shortcode tag.
 *
 * @since NEXT
 */

namespace AnyS\Elementor;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Core\DynamicTags\Manager as Tags_Manager;

// Checked and exited if Elementor has not been loaded yet.
if ( ! did_action( 'elementor/loaded' ) ) {
	return;
}

/**
 * Hooked to register dynamic tags.
 *
 * @since NEXT
 */
add_action( 'elementor/dynamic_tags/register', __NAMESPACE__ . '\\register_anys_dynamic_tags' );

/**
 * Registers the group and the Shortcode tag class.
 *
 * @since NEXT
 *
 * @param Tags_Manager $dynamic_tags Manager instance.
 * 
 * @return void
 */
function register_anys_dynamic_tags( Tags_Manager $dynamic_tags ) {
	// Group is registered.
	$dynamic_tags->register_group( 'anything-shortcodes', [
		'title' => __( 'Anything Shortcodes', 'anys' ),
	] );

	// Tag is registered.
	require_once __DIR__ . '/Tags/Shortcode_Tag.php';
	$dynamic_tags->register( new Tags\Shortcode_Tag() );
}
