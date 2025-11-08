<?php

namespace AnyS\Modules\Shortcodes\Types;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Handles post loops with optional [anys else] fallback.
 *
 * Handles the `[anys type="loop"] ... [anys else] ... [/anys]` shortcode.
 *
 * Example:
 * [anys type="loop" name="post" post_type="post" posts_per_page="3"]
 *   <h3>[anys type="post-field" name="post_title"]</h3>
 * [anys else]
 *   <p>No posts found.</p>
 * [/anys]
 *
 * @since NEXT
 */
final class Loop extends Base {
    use Singleton;

    public function get_type() {
        return 'loop';
    }

    protected function get_defaults() {
        return [
            'name'             => 'post',
            'post_type'        => 'post',
            'posts_per_page'   => 3,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'author'           => '',
            'paged'            => 1,
            'offset'           => 0,
            'post_status'      => 'publish',
            's'                => '',
            'meta_key'         => '',
            'meta_value'       => '',
            'meta_compare'     => '',
            'meta_query'       => '',
            'tax_query'        => '',
            'search_in'        => '',
            'exclude_current'  => '0',
            'before'           => '',
            'after'            => '',
            'fallback'         => '',
            'format'           => '',
        ];
    }

    /**
     * Renders the shortcode.
     *
     * @since NEXT
     *
     * @param array  $attributes Shortcode attributes.
     * @param string $content    Enclosed content (optional).
     *
     * @return string
     */
    public function render( array $attributes, string $content ) {
        // Parse dynamic attributes first.
        $attributes = $this->get_attributes( $attributes );
        $attributes = anys_parse_dynamic_attributes( $attributes );

        // Validate provider.
        $provider_name = strtolower( trim( (string) ( $attributes['name'] ?? '' ) ) );
        if ( $provider_name !== 'post' ) {
            return '';
        }

        // Extract item and else templates.
        $templates        = anys_split_else_block( (string) ( $content ?? '' ) );
        $item_template    = $templates['item'];
        $else_template    = $templates['else'];

        // Build WP_Query args.
        $query_args = $this->anys_build_wp_query_args( $attributes );
        $query_args = $this->anys_apply_search_columns( $query_args, $attributes );

        // Exclude container post if needed.
        $exclude_disabled = ( isset( $attributes['exclude_current'] ) && (string) $attributes['exclude_current'] === '0' );
        if ( ! $exclude_disabled ) {
            $container_post_id = $this->anys_detect_container_post_id();
            if ( $this->anys_should_exclude_container( $query_args, $attributes, $container_post_id ) ) {
                $query_args['post__not_in'] = isset( $query_args['post__not_in'] ) && is_array( $query_args['post__not_in'] )
                    ? array_unique( array_merge( $query_args['post__not_in'], [ $container_post_id ] ) )
                    : [ $container_post_id ];
            }
        }

        // Run query.
        $wp_query_instance = new \WP_Query( $query_args );

        // Handle empty state.
        if ( ! $wp_query_instance->have_posts() ) {
            if ( $else_template !== '' ) {
                return wp_kses_post( do_shortcode( $else_template ) );
            }
            wp_reset_postdata();
            return '';
        }

        // Render loop items.
        $final_output = '';
        while ( $wp_query_instance->have_posts() ) {
            $wp_query_instance->the_post();
            $final_output .= anys_render_template_fast( $item_template );
        }
        wp_reset_postdata();

        // Apply before/after/fallback.
        $final_output = anys_wrap_output( $final_output, $attributes );

        // Return sanitized output.
        return wp_kses_post( $final_output );
    }

    /**
     * Builds sanitized WP_Query args from shortcode attributes.
     *
     * @since NEXT
     *
     * @param array $atts
     *
     * @return array
     */
    private function anys_build_wp_query_args( array $atts ): array {
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
     * @param array $query_args
     * @param array $atts
     *
     * @return array
     */
    private function anys_apply_search_columns( array $query_args, array $atts ): array {
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
    private function anys_detect_container_post_id(): int {
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
     * @param array $query_args
     * @param array $atts
     * @param int                 $container_id
     *
     * @return bool
     */
    private function anys_should_exclude_container( array $query_args, array $atts, int $container_id ): bool {
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
}
