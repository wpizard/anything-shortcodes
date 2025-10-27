<?php
/**
 * Renders the Views tab content.
 *
 * @since NEXT
 */

defined( 'ABSPATH' ) || exit;

$options    = get_option( 'anys', [] );
$form_nonce = wp_create_nonce( 'anys_save_settings' );

// Normalizes current values.
$enable_views_shortcodes = ! empty( $options['views_enable_shortcodes'] );
$default_view            = isset( $options['views_default'] ) ? (string) $options['views_default'] : 'list';
$title_mode              = isset( $options['views_title_mode'] ) ? (string) $options['views_title_mode'] : 'plain';
$items_per_row           = isset( $options['views_items_per_row'] ) ? (int) $options['views_items_per_row'] : 3;
$template_html           = isset( $options['views_template_html'] ) ? (string) $options['views_template_html'] : '';

// Collects the form content in a buffer.
ob_start();
?>

<!-- Enables shortcodes rendering inside view contexts. -->
<div class="anys-field-group">
	<div class="anys-field-label">
		<?php echo esc_html__( 'Enable Shortcodes', 'anys' ); ?>
	</div>
	<div class="anys-field-control">
		<label for="anys_views_enable_shortcodes">
			<input
				type="checkbox"
				id="anys_views_enable_shortcodes"
				name="anys[views_enable_shortcodes]"
				value="1"
				<?php checked( $enable_views_shortcodes ); ?>
			/>
			<?php echo esc_html__( 'Enables shortcode parsing in view output.', 'anys' ); ?>
		</label>
		<p class="description">
			<?php echo esc_html__( 'Parses shortcodes before rendering the final view markup.', 'anys' ); ?>
		</p>
	</div>
</div>

<!-- Selects a default view type. -->
<div class="anys-field-group">
	<div class="anys-field-label">
		<label for="anys_views_default"><?php echo esc_html__( 'Default View', 'anys' ); ?></label>
	</div>
	<div class="anys-field-control">
		<select id="anys_views_default" name="anys[views_default]">
			<option value="list"   <?php selected( $default_view, 'list' ); ?>><?php echo esc_html__( 'List', 'anys' ); ?></option>
			<option value="grid"   <?php selected( $default_view, 'grid' ); ?>><?php echo esc_html__( 'Grid', 'anys' ); ?></option>
			<option value="custom" <?php selected( $default_view, 'custom' ); ?>><?php echo esc_html__( 'Custom Template', 'anys' ); ?></option>
		</select>
		<p class="description">
			<?php echo esc_html__( 'Specifies the default rendering style for views.', 'anys' ); ?>
		</p>
	</div>
</div>

<!-- Chooses how titles are rendered. -->
<div class="anys-field-group">
	<div class="anys-field-label">
		<?php echo esc_html__( 'Title Rendering', 'anys' ); ?>
	</div>
	<div class="anys-field-control">
		<?php
		$title_modes = [
			'plain'   => esc_html__( 'Plain text', 'anys' ),
			'escaped' => esc_html__( 'Escaped HTML', 'anys' ),
			'raw'     => esc_html__( 'Raw (no escaping)', 'anys' ),
		];
		foreach ( $title_modes as $val => $label ) :
			$field_id = 'anys_views_title_mode_' . $val;
			?>
			<label for="<?php echo esc_attr( $field_id ); ?>" style="margin-right:16px;">
				<input
					type="radio"
					id="<?php echo esc_attr( $field_id ); ?>"
					name="anys[views_title_mode]"
					value="<?php echo esc_attr( $val ); ?>"
					<?php checked( $title_mode, $val ); ?>
				/>
				<?php echo esc_html( $label ); ?>
			</label>
		<?php endforeach; ?>
		<p class="description">
			<?php echo esc_html__( 'Controls escaping strategy for view titles.', 'anys' ); ?>
		</p>
	</div>
</div>

<!-- Sets items per row for grid-like views. -->
<div class="anys-field-group">
	<div class="anys-field-label">
		<label for="anys_views_items_per_row"><?php echo esc_html__( 'Items Per Row', 'anys' ); ?></label>
	</div>
	<div class="anys-field-control">
		<input
			type="number"
			min="1"
			max="12"
			step="1"
			id="anys_views_items_per_row"
			name="anys[views_items_per_row]"
			value="<?php echo esc_attr( $items_per_row ); ?>"
		/>
		<p class="description">
			<?php echo esc_html__( 'Applies to grid layouts. Ignored by list and custom templates.', 'anys' ); ?>
		</p>
	</div>
</div>

<!-- Provides a custom HTML template (used when Default View = Custom Template). -->
<div class="anys-field-group">
	<div class="anys-field-label">
		<label for="anys_views_template_html"><?php echo esc_html__( 'Custom Template', 'anys' ); ?></label>
	</div>
	<div class="anys-field-control">
		<textarea
			id="anys_views_template_html"
			name="anys[views_template_html]"
			rows="8"
			cols="50"
			class="large-text code"
			placeholder="<?php echo esc_attr__( 'Enter custom HTML. Use placeholders like {{title}} and {{content}}.', 'anys' ); ?>"
		><?php echo esc_textarea( $template_html ); ?></textarea>
		<p class="description">
			<?php echo esc_html__( 'Renders when the default view is set to Custom Template.', 'anys' ); ?>
		</p>
	</div>
</div>

<?php
$content = ob_get_clean();

// Loads base layout.
include __DIR__ . '/layout.php';
