<?php
defined( 'ABSPATH' ) || exit;

$options    = get_option( 'anys', [] );
$form_nonce = wp_create_nonce( 'anys_save_settings' );

$whitelisted_raw = $options['whitelisted_functions'] ?? [];

if ( is_string( $whitelisted_raw ) ) {
    // String is split safely on any line ending.
    $lines = preg_split( "/\r\n|\r|\n/", $whitelisted_raw );
    // Values are trimmed and empty lines are removed.
    $whitelisted = array_values( array_filter( array_map( 'trim', (array) $lines ), 'strlen' ) );

} elseif ( is_array( $whitelisted_raw ) ) {
    // Array values are trimmed and empty entries are removed.
    $whitelisted = array_values( array_filter( array_map( 'trim', $whitelisted_raw ), 'strlen' ) );

} else {
    // Empty array is returned when data type is invalid.
    $whitelisted = [];
}

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
        ><?php echo esc_textarea( implode( "\n", $whitelisted ) ); ?></textarea>

        <p class="description">
            <?php echo esc_html__( 'Enter PHP functions allowed for the [anys] shortcode. One per line.', 'anys' ); ?>
        </p>
    </div>
</div>
<?php
$content = ob_get_clean();

include __DIR__ . '/layout.php';
