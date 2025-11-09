<?php

namespace AnyS\Modules\Settings;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Registers the settings page in WP Admin and handles saving plugin options.
 *
 * @since NEXT
 */
final class Settings_Page {
	use Singleton;

	/**
	 * Option name for saving settings.
	 *
	 * @var string
	 */
	private $option_name = 'anys';

	/**
	 * Slug for the settings page.
	 *
	 * @var string
	 */
	private $page_slug = 'anys-settings';

	/**
	 * Supported tabs.
	 *
	 * @var array<string,string>
	 */
	private $tabs = [];

	/**
	 * Adds WordPress admin hooks.
	 *
	 * @return void
	 */
	protected function add_hooks() {
		add_action( 'admin_menu', [ $this, 'register_menu_page' ] );
		add_action( 'admin_init', [ $this, 'handle_save' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

		$this->tabs = [
			'general'      => __( 'General', 'anys' ),
			'integrations' => __( 'Integrations', 'anys' ),
			'functions'    => __( 'Functions', 'anys' ),
			'views'        => __( 'Views', 'anys' ),
		];
	}

	/**
	 * Adds the settings page under WP Admin → Settings.
	 *
	 * @return void
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
	 * Handles saving settings via POST form.
	 *
	 * @return void
	 */
	public function handle_save() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if (
			isset( $_POST['anys'], $_POST['_anys_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_anys_nonce'] ) ), 'anys_save_settings' )
		) {
			$incoming = is_array( $_POST['anys'] ?? null ) ? wp_unslash( $_POST['anys'] ) : [];
			$existing = get_option( $this->option_name, [] );

			if ( ! is_array( $existing ) ) {
				$existing = [];
			}

			$merged = self::merge_and_sanitize_settings( $existing, $incoming );
			update_option( $this->option_name, $merged );

			wp_safe_redirect(
				add_query_arg(
					[
						'page'    => $this->page_slug,
						'tab'     => $this->current_tab_slug(),
						'updated' => 'true',
					],
					admin_url( 'options-general.php' )
				)
			);
			exit;
		}
	}

	/**
	 * Renders the settings page and active tab.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$active_tab = $this->current_tab_slug();

		echo '<div class="anys-wrap">';

		echo '<div class="anys-page-header">';
		echo '<h1 class="anys-title">' . esc_html__( 'Anything Shortcodes', 'anys' ) . '</h1>';
		echo '<a class="anys-pro-cta" href="#" target="_blank" rel="noopener">'
			. esc_html__( 'Unlock Extra Features with Anything Shortcodes PRO', 'anys' ) .
		'</a>';
		echo '</div>';

		// Tabs
		echo '<h2 class="nav-tab-wrapper anys-tabs">';
		foreach ( $this->tabs as $slug => $label ) {
			$url    = add_query_arg(
				[ 'page' => $this->page_slug, 'tab' => 'anys-' . $slug ],
				admin_url( 'options-general.php' )
			);
			$active = $slug === $active_tab ? ' nav-tab-active' : '';
			echo '<a href="' . esc_url( $url ) . '" class="nav-tab anys-tab' . $active . '">' . esc_html( $label ) . '</a>';
		}
		echo '</h2>';

		$view_file = $this->view_path( $active_tab );
		if ( file_exists( $view_file ) ) {
			$options    = get_option( $this->option_name, [] );
			$form_nonce = wp_create_nonce( 'anys_save_settings' );
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'The requested settings tab could not be found.', 'anys' ) . '</p>';
		}

		echo '</div>';
	}

	/**
	 * Returns current active tab slug.
	 *
	 * @return string
	 */
	private function current_tab_slug(): string {
		$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

		if ( $requested && 0 === strpos( $requested, 'anys-' ) ) {
			$requested = substr( $requested, 5 );
		}

		return array_key_exists( $requested, $this->tabs ) ? $requested : 'general';
	}

	/**
	 * Returns the path to the view file of the tab.
	 *
	 * @param string $tab Tab slug.
	 * @return string
	 */
	private function view_path( string $tab ): string {
		return __DIR__ . '/views/' . $tab . '.php';
	}

	/**
	 * Enqueues admin CSS and JS for settings page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_' . $this->page_slug !== $hook ) {
			return;
		}

		wp_enqueue_style( 'anys-admin-settings', ANYS_CSS_URL . 'settings.css', [], 'NEXT' );
		wp_enqueue_script( 'anys-admin-mobile-sidebar', ANYS_JS_URL . 'admin-mobile-sidebar.js', [], '1.0.0', true );
	}

	/**
	 * Merges and sanitizes plugin settings recursively.
	 *
	 * @param array $existing Existing options.
	 * @param array $new New submitted options.
	 * @return array Sanitized merged options.
	 */
	private static function merge_and_sanitize_settings( array $existing, array $new ): array {
		$merged = array_replace_recursive( $existing, $new );

		if ( isset( $merged['whitelisted_functions'] ) ) {
			$list = $merged['whitelisted_functions'];
			if ( is_string( $list ) ) {
				$list = preg_split( "/\r\n|\r|\n/", $list );
			}
			$merged['whitelisted_functions'] = array_values(
				array_unique(
					array_filter( array_map( 'trim', (array) $list ), 'strlen' )
				)
			);
		}

		array_walk_recursive(
			$merged,
			function ( &$v ) {
				if ( is_string( $v ) ) {
					$v = sanitize_text_field( $v );
				}
			}
		);

		return $merged;
	}
}

// Boot the settings page.
Settings_Page::get_instance();
