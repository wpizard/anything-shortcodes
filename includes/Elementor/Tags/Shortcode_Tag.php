<?php
/**
 * Dynamic Tag: Shortcode
 *
 * @since NEXT
 */

namespace AnyS\Elementor\Tags;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

class Shortcode_Tag extends Tag {

	/**
	 * Returns the unique slug.
	 *
	 * @since NEXT
	 */
	public function get_name() {
		return 'anything-shortcode';
	}

	/**
	 * Returns the tag title.
	 *
	 * @since NEXT
	 */
	public function get_title() {
		return __( 'Shortcode', 'anys' );
	}

	/**
	 * Returns the registered group.
	 *
	 * @since NEXT
	 */
	public function get_group() {
		return 'anything-shortcodes';
	}

	/**
	 * Makes tag available in text-type fields.
	 *
	 * @since NEXT
	 */
	public function get_categories() {
		return [ 'text' ];
	}

	/**
	 * Registers the shortcode input control.
	 *
	 * @since NEXT
	 */
	protected function register_controls() {
		$this->add_control(
			'shortcode',
			[
				'label'       => __( 'Shortcode', 'anys' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '[my_shortcode attr="value"]',
				'label_block' => true,
			]
		);
	}

	/**
	 * Renders the shortcode output.
	 *
	 * @since NEXT
	 */
	public function render() {
		$shortcode = (string) $this->get_settings( 'shortcode' );

		if ( trim( $shortcode ) === '' ) {
			echo '';
			return;
		}

		$shortcode = wp_kses_post( $shortcode );
		$output    = do_shortcode( $shortcode );

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
