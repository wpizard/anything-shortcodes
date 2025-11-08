<?php
/**
 * Renders the Integrations tab content.
 *
 * @since NEXT
 */

defined( 'ABSPATH' ) || exit;

$options    = get_option( 'anys', [] );
$form_nonce = wp_create_nonce( 'anys_save_settings' );

// Normalizes current values.
$enabled         = ! empty( $options['integrations_enabled'] );
$provider        = isset( $options['integration_provider'] ) ? (string) $options['integration_provider'] : 'none';
$auth_method     = isset( $options['integration_auth_method'] ) ? (string) $options['integration_auth_method'] : 'oauth';
$api_token       = isset( $options['integration_api_token'] ) ? (string) $options['integration_api_token'] : '';

// Collects the form content in a buffer.
ob_start();
?>
<div class="anys-field-group">
    <div class="anys-field-label">
        <?php echo esc_html__( 'Enable Integrations', 'anys' ); ?>
    </div>
    <div class="anys-field-control">
        <label for="anys_integrations_enabled">
            <input
                type="checkbox"
                id="anys_integrations_enabled"
                name="anys[integrations_enabled]"
                value="1"
                <?php checked( $enabled ); ?>
            />
            <?php echo esc_html__( 'Enables third-party integrations globally.', 'anys' ); ?>
        </label>
        <p class="description">
            <?php echo esc_html__( 'Toggles all integration features for the plugin.', 'anys' ); ?>
        </p>
    </div>
</div>

<div class="anys-field-group">
    <div class="anys-field-label">
        <label for="anys_integration_provider"><?php echo esc_html__( 'Provider', 'anys' ); ?></label>
    </div>
    <div class="anys-field-control">
        <select id="anys_integration_provider" name="anys[integration_provider]">
            <option value="none"   <?php selected( $provider, 'none' );   ?>><?php echo esc_html__( 'None', 'anys' ); ?></option>
            <option value="google" <?php selected( $provider, 'google' ); ?>><?php echo esc_html__( 'Google', 'anys' ); ?></option>
            <option value="zapier" <?php selected( $provider, 'zapier' ); ?>><?php echo esc_html__( 'Zapier', 'anys' ); ?></option>
            <option value="make"   <?php selected( $provider, 'make' );   ?>><?php echo esc_html__( 'Make (Integromat)', 'anys' ); ?></option>
        </select>
        <p class="description">
            <?php echo esc_html__( 'Selects the primary integration provider.', 'anys' ); ?>
        </p>
    </div>
</div>

<div class="anys-field-group">
    <div class="anys-field-label">
        <?php echo esc_html__( 'Authentication', 'anys' ); ?>
    </div>
    <div class="anys-field-control">
        <?php
        $auth_opts = [
            'oauth' => esc_html__( 'OAuth', 'anys' ),
            'token' => esc_html__( 'API Token', 'anys' ),
        ];
        foreach ( $auth_opts as $val => $label ) :
            $field_id = 'anys_integration_auth_method_' . $val;
            ?>
            <label for="<?php echo esc_attr( $field_id ); ?>" style="margin-right:16px;">
                <input
                    type="radio"
                    id="<?php echo esc_attr( $field_id ); ?>"
                    name="anys[integration_auth_method]"
                    value="<?php echo esc_attr( $val ); ?>"
                    <?php checked( $auth_method, $val ); ?>
                />
                <?php echo esc_html( $label ); ?>
            </label>
        <?php endforeach; ?>
        <p class="description">
            <?php echo esc_html__( 'Chooses the authentication strategy for the selected provider.', 'anys' ); ?>
        </p>
    </div>
</div>

<div class="anys-field-group">
    <div class="anys-field-label">
        <label for="anys_integration_api_token"><?php echo esc_html__( 'API Token', 'anys' ); ?></label>
    </div>
    <div class="anys-field-control">
        <input
            type="text"
            id="anys_integration_api_token"
            name="anys[integration_api_token]"
            class="regular-text"
            value="<?php echo esc_attr( $api_token ); ?>"
            placeholder="<?php echo esc_attr__( 'Paste token here (if using token auth).', 'anys' ); ?>"
        />
        <p class="description">
            <?php echo esc_html__( 'Provides a token for providers that require token-based access.', 'anys' ); ?>
        </p>
    </div>
</div>
<?php
$content = ob_get_clean();

// Loads layout.
include __DIR__ . '/layout.php';
