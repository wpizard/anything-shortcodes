<?php
/**
 * Renders the Functions tab content.
 *
 * @since NEXT
 */

defined( 'ABSPATH' ) || exit;

$options    = get_option( 'anys', [] );
$form_nonce = wp_create_nonce( 'anys_save_settings' );

// Collects the form content in a buffer.
ob_start();
?>
<div class="anys-field-group">
	<div class="anys-field-label">
		<label for="anys_whitelisted_functions">
			<?php echo esc_html__( 'Whitelisted Functions', 'anys' ); ?>
		</label>
	</div>

	<div class="anys-field-control">
		<textarea
			id="anys_whitelisted_functions"
			name="anys[whitelisted_functions]"
			rows="10"
			cols="50"
			class="large-text code"
			placeholder="<?php echo esc_attr__( 'Enter one function name per line', 'anys' ); ?>"
		><?php echo esc_textarea( implode( "\n", $options['whitelisted_functions'] ?? [] ) ); ?></textarea>

		<p class="description">
			<?php echo esc_html__( 'Enter PHP functions allowed for the [anys] shortcode. One per line.', 'anys' ); ?>
		</p>
	</div>
</div>
<?php
$content = ob_get_clean();

// Loads layout.
include __DIR__ . '/layout.php';
