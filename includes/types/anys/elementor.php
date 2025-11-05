<?php
/**
 * Renders the [anys type="elementor" name="template" id="123"] shortcode output.
 *
 * Renders a saved Elementor template (Section or Page) by its post ID and includes
 * all required Elementor assets for correct styling and behavior.
 *
 * Expected attributes:
 * - name: Must be "template" (required)
 * - id: Elementor template post ID (required)
 * - before: Content to prepend before the rendered output (optional)
 * - after: Content to append after the rendered output (optional)
 * - fallback: Content to display if the template cannot be rendered (optional)
 *
 * @since NEXT
 */

// Validate name.
if ( empty( $attributes['name'] ) || 'template' !== strtolower( (string) $attributes['name'] ) ) {
    echo '';

    return;
}

// Check Elementor status.
if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
    $value = esc_html__( 'Elementor is not active.', 'anys' );

    echo wp_kses_post( anys_wrap_output( $value, $attributes ) );

    return;
}

// Validate ID.
$id = isset( $attributes['id'] ) ? absint( $attributes['id'] ) : 0;
if ( ! $id ) {
    $value = esc_html__( 'Missing or invalid "id" attribute.', 'anys' );

    echo wp_kses_post( anys_wrap_output( $value, $attributes ) );

    return;
}

// Fetch post.
$post = get_post( $id );
if ( ! $post ) {
    $value = esc_html__( 'Template not found.', 'anys' );

    echo wp_kses_post( anys_wrap_output( $value, $attributes ) );

    return;
}

// Check post type.
if ( 'elementor_library' !== get_post_type( $post ) ) {
    $value = esc_html__( 'The provided ID is not an Elementor template.', 'anys' );

    echo wp_kses_post( anys_wrap_output( $value, $attributes ) );

    return;
}

// Load Elementor assets.
anys_maybe_enqueue_elementor_assets( $attributes );

// Read template type.
$template_type = get_post_meta( $id, '_elementor_template_type', true ); // 'section' | 'page'

if ( $template_type && ! in_array( $template_type, array( 'section', 'page' ), true ) ) {
    $value = esc_html__( 'Unsupported Elementor template type.', 'anys' );

    echo wp_kses_post( anys_wrap_output( $value, $attributes ) );

    return;
}

// Prevent recursion.
if ( is_singular( 'elementor_library' ) && get_the_ID() === $id ) {
    $value = esc_html__( 'Recursive rendering detected.', 'anys' );

    echo wp_kses_post( anys_wrap_output( $value, $attributes ) );

    return;
}

// Build cache key.
$cache_key = 'anys_elem_tpl_' . $id . '_' . (string) strtotime( $post->post_modified_gmt ) . '_' . ( $template_type ?: 'na' );
$cached    = get_transient( $cache_key );

// Return cached version if found.
if ( false !== $cached ) {
    echo anys_wrap_output( $cached, $attributes ); // raw

    return;
}

try {
    // Render template.
    $html = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id, true );

    // Wrap section if needed.
    if ( 'section' === $template_type ) {
        $html = '<div class="anys-elementor-section">' . $html . '</div>';
    }

    // Empty check.
    if ( ! $html ) {
        $value = esc_html__( 'Template is empty or cannot be rendered.', 'anys' );

        echo wp_kses_post( anys_wrap_output( $value, $attributes ) );

        return;
    }

    // Save to cache.
    set_transient( $cache_key, $html, MINUTE_IN_SECONDS * 10 );

    // Output final HTML.
    echo anys_wrap_output( $html, $attributes ); // raw
} catch ( \Throwable ) {
    // Handle render error.
    $value = esc_html__( 'An error occurred while rendering the template.', 'anys' );
    echo wp_kses_post( anys_wrap_output( $value, $attributes ) );
}
