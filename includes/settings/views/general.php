<?php
/**
 * Renders the General tab content.
 *
 * @since NEXT
 */

defined( 'ABSPATH' ) || exit;

$options = get_option( 'anys', [] );
$form_nonce = wp_create_nonce( 'anys_save_settings' );

// Collects the form content in a buffer.
ob_start();
?>
<div class="anys-field-group">
	<div class="anys-field-label">
		<label for="anys_site_title"><?php echo esc_html__( 'Site Title', 'anys' ); ?></label>
	</div>
	<div class="anys-field-control">
		<input
			type="text"
			id="anys_site_title"
			name="anys[site_title]"
			class="regular-text"
			value="<?php echo esc_attr( $options['site_title'] ?? '' ); ?>"
		/>
		<p class="description">
			<?php echo esc_html__( 'Sets a sample site title option.', 'anys' ); ?>
		</p>
	</div>
</div>
<?php
$content = ob_get_clean();
$title   = esc_html__( 'General Settings', 'anys' );

include __DIR__ . '/layout.php';
