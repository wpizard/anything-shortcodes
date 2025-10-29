<?php
/**
 * Anything Shortcodes – Settings bootstrap.
 *
 * Registers the settings page in WP Admin, routes tabs, and persists plugin options.
 *
 * @since NEXT
 */

namespace AnyS;

defined( 'ABSPATH' ) || exit;

/**
 * Manages the Anything Shortcodes settings page in WP Admin.
 *
 * Handles menu registration, tab routing, and saving settings options.
 *
 * @since NEXT
 */
final class Anys_Settings_Page {

	/** Option name used to store all settings. */
	private $option_name = 'anys';

	/** Slug used for the settings page. */
	private $page_slug = 'anys-settings';

	/** Supported tabs and labels. */
	private $tabs = [
		'general'      => 'General',
		'integrations' => 'Integrations',
		'functions'    => 'Functions',
		'views'        => 'Views',
	];

	/** @var self Singleton instance. */
	private static $instance;

	/**
	 * Returns the singleton instance.
	 *
	 * @since NEXT
	 *
	 * @return self Instance.
	 */
	public static function get_instance(): self {
		return self::$instance ?? ( self::$instance = new self() );
	}

	/**
	 * Initializes the class by registering admin hooks.
	 *
	 * @since NEXT
	 */
	private function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu_page' ] );
		add_action( 'admin_init', [ $this, 'handle_save' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	/** Prevents cloning. */
	private function __clone() {}

	/** Prevents unserialization. */
	private function __wakeup() {}

	/**
	 * Registers the settings page under WP Admin → Settings.
	 *
	 * @since NEXT
	 */
	public function register_menu_page() {
		add_options_page(
			__( 'Anything Shortcodes', 'anys' ),
			__( 'Anything Shortcodes', 'anys' ),
			'manage_options',
			$this->page_slug,
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Handles saving the settings array.
	 *
	 * @since NEXT
	 */
	public function handle_save() {
		// Verifies admin context and permissions.
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Validates nonce and persists options.
		if (
			isset( $_POST['anys'], $_POST['_anys_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_anys_nonce'] ) ), 'anys_save_settings' )
		) {
			$incoming = is_array( $_POST['anys'] ?? null ) ? wp_unslash( $_POST['anys'] ) : [];

			// Get existing options.
            $existing = get_option( $this->option_name, [] );
            if ( ! is_array( $existing ) ) {
                $existing = [];
            }

            // Sanitize & merge recursively (new values override old ones).
            $merged = self::merge_and_sanitize_settings( $existing, $incoming );

            // Persist merged options.
            update_option( $this->option_name, $merged );

			// Redirects to avoid form resubmission.
			$redirect = add_query_arg(
				[
					'page'    => $this->page_slug,
					'tab'     => $this->current_tab_slug(),
					'updated' => 'true',
				],
				admin_url( 'options-general.php' )
			);
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Renders the settings page and includes the active tab view.
	 *
	 * @since NEXT
	 */
	public function render_page() {
        // Verifies capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $active_tab = $this->current_tab_slug();
        $tabs       = $this->tabs;

        echo '<div class="anys-wrap">';

        // Renders page header with title and PRO CTA.
        echo '<div class="anys-page-header">';
        echo '<h1 class="anys-title">' . esc_html__( 'Anything Shortcodes', 'anys' ) . '</h1>';
        echo '<a class="anys-pro-cta" href="#" target="_blank" rel="noopener">'
                . esc_html__( 'Unlock Extra Features with Anything Shortcodes PRO', 'anys' ) .
            '</a>';
        echo '</div>';

        // Renders tab navigation.
        echo '<h2 class="nav-tab-wrapper anys-tabs">';
        foreach ( $tabs as $slug => $label ) {
            $href   = esc_url(
                add_query_arg(
                    [
                        'page' => $this->page_slug,
                        'tab'  => 'anys-' . $slug,
                    ],
                    admin_url( 'options-general.php' )
                )
            );

            $active = ( $slug === $active_tab ) ? ' nav-tab-active' : '';

            echo '<a href="' . $href . '" class="nav-tab anys-tab' . $active . '">' . esc_html( $label ) . '</a>';
        }
        echo '</h2>';

        // Loads the view file for the active tab.
        $view_file = $this->view_path( $active_tab );

        if ( file_exists( $view_file ) ) {
            // Provides $options and nonce to the view.
            $options    = get_option( $this->option_name, [] );
            $form_nonce = wp_create_nonce( 'anys_save_settings' );

            include $view_file;

        } else {
            echo '<p>' . esc_html__( 'The requested settings tab could not be found.', 'anys' ) . '</p>';
        }

        echo '</div>';
    }

	/**
	 * Returns the sanitized current tab slug (without the "anys-" prefix).
	 *
	 * @since NEXT
	 *
	 * @return string Tab slug.
	 */
	private function current_tab_slug(): string {
		$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

		if ( $requested && 0 === strpos( $requested, 'anys-' ) ) {
			$requested = substr( $requested, 5 );
		}

		if ( ! $requested || ! array_key_exists( $requested, $this->tabs ) ) {
			return 'general';
		}

		return $requested;
	}

	/**
	 * Returns the absolute path to a view file for the given tab.
	 *
	 * @since NEXT
	 *
	 * @param string $tab Tab slug.
	 * @return string Absolute path.
	 */
	private function view_path( string $tab ): string {
        error_log( 'Loading view for tab: ' . __DIR__ . '/views/' . $tab . '.php' );
		return __DIR__ . '/views/' . $tab . '.php';
	}

    /**
     * Enqueues admin CSS for the settings page.
     *
     * @since NEXT
     */
    public function enqueue_admin_assets( $hook ) {
        // Ensures style loads only on our settings page.
        if ( 'settings_page_' . $this->page_slug !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'anys-admin-settings',
            ANYS_CSS_URL . 'settings.css',
            [],
            'NEXT'
        );

        wp_enqueue_script(
            'anys-admin-mobile-sidebar',
            ANYS_JS_URL . 'admin-mobile-sidebar.js',
            array(),
            '1.0.0',
            true
        );
    }

    /**
     * Merges and sanitizes plugin settings recursively.
     *
     * Existing options are preserved unless replaced.
     * Strings are sanitized and arrays are merged recursively.
     * Special handling is applied for the 'whitelisted_functions' field.
     *
     * @since NEXT
     *
     * @param array $existing_options Previously saved options.
     * @param array $new_submitted_options Newly submitted options.
     * 
     * @return array Sanitized merged options.
     */
    private static function merge_and_sanitize_settings( array $existing_options, array $new_submitted_options ): array {
        // Existing and new settings are merged (new replaces old).
        $merged_options = array_replace_recursive( $existing_options, $new_submitted_options );

        // 'whitelisted_functions' field is normalized.
        if ( isset( $merged_options['whitelisted_functions'] ) ) {
            $raw_whitelisted = $merged_options['whitelisted_functions'];

            if ( is_string( $raw_whitelisted ) ) {
                $lines = preg_split( "/\r\n|\r|\n/", $raw_whitelisted );
                $function_list = array_map( 'trim', (array) $lines );
            } elseif ( is_array( $raw_whitelisted ) ) {
                $function_list = array_map( 'trim', $raw_whitelisted );
            } else {
                $function_list = [];
            }

            // Empty and duplicate entries are removed.
            $merged_options['whitelisted_functions'] = array_values(
                array_unique( array_filter( $function_list, 'strlen' ) )
            );
        }

        // Recursive sanitization is applied to scalar values.
        array_walk_recursive( $merged_options, function ( &$value ) {
            if ( is_string( $value ) ) {
                $value = sanitize_text_field( $value );
            } elseif ( is_bool( $value ) ) {
                $value = (bool) $value;
            } elseif ( is_numeric( $value ) ) {
                // Numeric values are kept as-is.
            }
        } );

        return $merged_options;
    }


}

/** Boots the singleton immediately when this file loads. */
Anys_Settings_Page::get_instance();
