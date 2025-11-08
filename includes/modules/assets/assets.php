<?php

namespace AnyS\Modules;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Assets module.
 *
 * Handles registration and localization of assets.
 *
 * @since NEXT
 */
final class Assets {
    use Singleton;

    /**
     * Adds hooks.
     *
     * @since NEXT
     */
    protected function add_hooks() {
        add_action( 'init', [ $this, 'register_assets' ] );
        add_action( 'wp_head', [ $this, 'localize_data' ] );
    }

    /**
     * Gets assets.
     *
     * @since NEXT
     */
    protected function get_assets() {
        $assets = [
            'styles' => [
                'anys-utilities' => [
                    'src'       => ANYS_CSS_URL . 'utilities.css',
                    'deps'      => [],
                    'version'   => ANYS_VERSION,
                    'media'     => 'all',
                ],
            ],
            'scripts' => [
                'anys-utilities' => [
                    'src'       => ANYS_JS_URL . 'utilities.js',
                    'deps'      => [],
                    'version'   => ANYS_VERSION,
                    'in_footer' => true,
                ],
            ],
        ];

        return $assets;
    }

    /**
     * Registers assets.
     *
     * @since NEXT
     */
    public function register_assets() {
        $assets = $this->get_assets();

        // Registers styles.
        if ( ! empty( $assets['styles'] ) ) {
            foreach ( $assets['styles'] as $handle => $style ) {
                wp_register_style(
                    $handle,
                    $style['src'],
                    $style['deps'] ?? [],
                    $style['version'] ?? false,
                    $style['media'] ?? 'all'
                );
            }
        }

        // Registers scripts.
        if ( ! empty( $assets['scripts'] ) ) {
            foreach ( $assets['scripts'] as $handle => $script ) {
                wp_register_script(
                    $handle,
                    $script['src'],
                    $script['deps'] ?? [],
                    $script['version'] ?? false,
                    $script['in_footer'] ?? true
                );
            }
        }
    }

    /**
     * Localizes data.
     *
     * @since NEXT
     */
    public function localize_data() {
        ?>
        <script>
            window.anysData = <?php echo wp_json_encode( [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'anys_nonce' ),
            ] ); ?>;
        </script>
        <?php
    }
}

/**
 * Initializes the module.
 *
 * @since NEXT
 */
Assets::get_instance();
