<?php
/**
 * Renders the [anys type="loop"] ... [anys else] ... [/anys] output.
 *
 * Usage:
 * [anys type="loop" name="post" post_type="post" posts_per_page="3"]
 *   <h3>[anys type="post-field" name="post_title"]</h3>
 * [anys else]
 *   <p>No posts found.</p>
 * [/anys]
 *
 * Providers:
 * - name="post" (WP_Query)
 *
 * Attributes (post):
 * - post_type, posts_per_page, orderby, order, author, paged, offset, post_status, s
 * - meta_key, meta_value, meta_compare
 * - meta_query (JSON), tax_query (JSON)
 * - search_in ("all"|"title"|"title_excerpt")
 * - exclude_current ("1"|"0")
 *
 * @since NEXT
 */

defined( 'ABSPATH' ) || exit;

// Validates attributes.
if ( ! isset( $attributes ) || ! is_array( $attributes ) ) {
	return;
}

// Validates provider.
$provider = isset( $attributes['name'] ) ? strtolower( trim( (string) $attributes['name'] ) ) : '';
if ( $provider !== 'post' ) {
	return;
}

// Parses dynamic attribute placeholders.
$attributes = function_exists( 'anys_parse_dynamic_attributes' )
	? anys_parse_dynamic_attributes( $attributes )
	: $attributes;

// Extracts item/else templates.
$templates     = anys_split_else_block( (string) ( $content ?? '' ) );
$item_template = $templates['item'];
$else_template = $templates['else'];

// Builds base WP_Query args from attributes.
$query_args = anys_build_wp_query_args( $attributes );

// Maps 'search_in' to 'search_columns'.
$query_args = anys_apply_search_columns( $query_args, $attributes );

// Excludes container post when it would self-match.
$exclude_disabled = ( isset( $attributes['exclude_current'] ) && (string) $attributes['exclude_current'] === '0' );
if ( ! $exclude_disabled ) {
	$container_id = anys_detect_container_post_id();
	if ( anys_should_exclude_container( $query_args, $attributes, $container_id ) ) {
		$query_args['post__not_in'] = isset( $query_args['post__not_in'] ) && is_array( $query_args['post__not_in'] )
			? array_unique( array_merge( $query_args['post__not_in'], [ $container_id ] ) )
			: [ $container_id ];
	}
}

// Runs query.
$wp_query_instance = new \WP_Query( $query_args );

// Handles empty state via [anys else].
if ( ! $wp_query_instance->have_posts() ) {
	if ( $else_template !== '' ) {
		echo wp_kses_post( do_shortcode( $else_template ) );
	}
	wp_reset_postdata();
	return;
}

// Renders items.
$final_output = '';
while ( $wp_query_instance->have_posts() ) {
	$wp_query_instance->the_post();
	$final_output .= anys_render_template_fast( $item_template ); // Avoids extra do_shortcode when not needed.
}
wp_reset_postdata();

// Applies before/after/fallback and outputs.
$final_output = anys_wrap_output( $final_output, $attributes );
echo wp_kses_post( $final_output );
