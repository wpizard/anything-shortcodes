<?php

namespace BS5PC;

defined( 'ABSPATH' ) || die();

/**
 * Settings Page Class.
 *
 * @since 1.1.0
 */
final class Settings_Page {

    /**
     * The instance.
     *
     * @since 1.1.0
     */
    private static $instance;

    /**
     * Returns the instance.
     *
     * @since 1.1.0
     *
     * @return Settings_Page
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 1.1.0
     */
    private function __construct() {
        $this->add_hooks();
    }

    /**
     * Adds hooks.
     *
     * @since 1.1.0
     */
    protected function add_hooks() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_filter( 'plugin_action_links_anything-shortcodes/anything-shortcodes.php', [ $this, 'modify_plugin_action_links' ] );
    }

    /**
     * Adds settings page under Settings menu.
     *
     * @since 1.1.0
     */
    public function add_settings_page() {
        add_options_page(
            esc_html__( 'Anything Shortcodes Settings', 'anys' ),
            esc_html__( 'Anything Shortcodes', 'anys' ),
            'manage_options',
            'anys-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Registers settings, sections, and fields.
     *
     * @since 1.1.0
     */
    public function register_settings() {
        register_setting(
            'anys_settings_group',
            'anys_settings',
            [ $this, 'sanitize_settings' ]
        );

        // Functions section.
        add_settings_section(
            'anys_functions_section',
            esc_html__( 'Functions', 'anys' ),
            [ $this, 'functions_section_callback' ],
            'anys-settings'
        );

        // Whitelist Functions field.
        add_settings_field(
            'anys_allowed_functions',
            esc_html__( 'Allowed Functions', 'anys' ),
            [ $this, 'allowed_functions_callback' ],
            'anys-settings',
            'anys_functions_section'
        );
    }

    /**
     * Sanitizes settings input.
     *
     * @since 1.1.0
     *
     * @param array $input Raw input values.
     *
     * @return array Sanitized values.
     */
    public function sanitize_settings( $input ) {
        $output = [];

        // Sanitizes whitelist textarea into trimmed array of function names.
        if ( isset( $input['anys_allowed_functions'] ) ) {
            $functions = explode( "\n", sanitize_textarea_field( $input['anys_allowed_functions'] ) );
            $functions = array_map( 'trim', $functions );
            $functions = array_filter( $functions ); // Remove empty lines

            $output['anys_allowed_functions'] = $functions;
        } else {
            $output['anys_allowed_functions'] = [];
        }

        return $output;
    }

    /**
     * Functions section description callback.
     *
     * @since 1.1.0
     */
    public function functions_section_callback() {
        printf( '<span>%s</span>',
            esc_html__( 'Adjust plugin functions.', 'anys' )
        );
    }

    /**
     * Renders the Allowed Functions textarea field.
     *
     * @since 1.1.0
     */
    public function allowed_functions_callback() {
        $options           = get_option( 'anys_settings' );
        $allowed_functions = isset( $options['anys_allowed_functions'] ) && is_array( $options['anys_allowed_functions'] )
            ? $options['anys_allowed_functions']
            : [];

        // Gets the default allowed functions list as an array.
        $default_allowed_functions = anys_get_default_allowed_functions();

        // Converts array to a comma-separated string for display.
        $default_allowed_functions_list = implode( ', ', $default_allowed_functions );
        ?>
        <textarea
            id="anys_allowed_functions"
            name="anys_settings[anys_allowed_functions]"
            rows="7"
            cols="50"
            placeholder="<?php esc_attr_e( 'Enter one function name per line', 'anys' ); ?>"
        ><?php echo esc_textarea( implode( "\n", $allowed_functions ) ); ?></textarea>
        <p class="description">
            <?php printf(
                esc_html__( 'Enter function names allowed for [anys] shortcode. One per line. Default allowed functions (%s)', 'anys' ),
                $default_allowed_functions_list
            ); ?>
        </p>
        <?php
    }

    /**
     * Outputs the settings page HTML.
     *
     * @since 1.1.0
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Anything Shortcodes Settings', 'anys' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'anys_settings_group' );
                    do_settings_sections( 'anys-settings' );
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Modifies plugin activation links to add Settings link.
     *
     * @since 1.1.0
     *
     * @param array $links Plugin action links.
     *
     * @return array Modified links.
     */
    public function modify_plugin_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'options-general.php?page=anys-settings' ) . '">' . esc_html__( 'Settings', 'anys' ) . '</a>';

        return $links;
    }
}

/**
 * Initializes the Settings_Page class.
 *
 * @since 1.1.0
 */
Settings_Page::get_instance();
