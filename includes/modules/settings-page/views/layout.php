<?php
defined( 'ABSPATH' ) || exit;

$content    = isset( $content ) ? (string) $content : '';
$form_nonce = isset( $form_nonce ) ? (string) $form_nonce : '';
?>

<div class="anys-settings-grid">
    <div class="anys-main">
        <!-- Renders the settings form. -->
        <form method="post" action="">
            <input type="hidden" name="_anys_nonce" value="<?php echo esc_attr( $form_nonce ); ?>">
            <?php
            // Outputs tab-specific fields markup.
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $content;
            ?>

            <?php submit_button( esc_html__( 'Save Changes', 'anys' ) ); ?>
        </form>
    </div>

    <!-- Desktop sidebar (hidden on mobile) -->
    <aside class="anys-sidebar" aria-label="<?php echo esc_attr__( 'Quick Links', 'anys' ); ?>">
        <h3><?php echo esc_html__( 'Quick Links', 'anys' ); ?></h3>
        <div class="anys-card">
            <ul class="anys-links">
                <li><a href="#" target="_blank" rel="noopener"><?php echo esc_html__( 'Website', 'anys' ); ?></a></li>
                <li><a href="#" target="_blank" rel="noopener"><?php echo esc_html__( 'Documentation', 'anys' ); ?></a></li>
                <li><a href="#" target="_blank" rel="noopener"><?php echo esc_html__( 'Feature Requests', 'anys' ); ?></a></li>
                <li><a href="#" target="_blank" rel="noopener"><?php echo esc_html__( 'Support', 'anys' ); ?></a></li>
            </ul>
        </div>
    </aside>
</div>

<!-- Mobile FAB + sheet (hidden on desktop) -->
<button type="button"
    class="anys-fab"
    aria-controls="anys-mobile-menu"
    aria-expanded="false"
    aria-label="<?php echo esc_attr__( 'Open quick links', 'anys' ); ?>">
    <!-- Simple icon -->
    <span aria-hidden="true">☰</span>
</button>

<div id="anys-mobile-menu" class="anys-mobile-sheet" role="dialog" aria-modal="true" aria-labelledby="anys-mobile-menu-title" hidden>
    <div class="anys-mobile-sheet__content">
        <h3 id="anys-mobile-menu-title"><?php echo esc_html__( 'Quick Links', 'anys' ); ?></h3>
        <ul class="anys-links">
            <li><a href="#" target="_blank" rel="noopener"><?php echo esc_html__( 'Website', 'anys' ); ?></a></li>
            <li><a href="#" target="_blank" rel="noopener"><?php echo esc_html__( 'Documentation', 'anys' ); ?></a></li>
            <li><a href="#" target="_blank" rel="noopener"><?php echo esc_html__( 'Feature Requests', 'anys' ); ?></a></li>
            <li><a href="#" target="_blank" rel="noopener"><?php echo esc_html__( 'Support', 'anys' ); ?></a></li>
        </ul>
        <button type="button" class="anys-mobile-sheet__close" aria-label="<?php echo esc_attr__( 'Close', 'anys' ); ?>">✕</button>
    </div>
    <div class="anys-mobile-sheet__backdrop"></div>
</div>
