<?php

namespace AnyS\Modules;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Cache module.
 *
 * Provides unified caching using object cache (if available)
 * and falls back to transients otherwise.
 *
 * Supports group-based caching and automatic key hashing.
 *
 * @since NEXT
 */
final class Cache {
    use Singleton;

    /**
     * Default prefix for cache keys.
     *
     * @since NEXT
     *
     * @var string
     */
    private string $prefix = 'anys_';

    /**
     * Whether persistent object cache is enabled.
     *
     * @since NEXT
     *
     * @var bool
     */
    private bool $object_cache_enabled = false;

    /**
     * Constructor.
     *
     * @since NEXT
     */
    private function __construct() {
        $this->object_cache_enabled = (bool) wp_using_ext_object_cache();

        $this->load_helpers();
    }

    /**
     * Gets a cached value.
     *
     * @since NEXT
     *
     * @param string $key   Cache key.
     * @param string $group Cache group (default: 'default').
     *
     * @return mixed Cached value or false if not found.
     */
    public function get( string $key, string $group = 'default' ): mixed {
        $key   = $this->get_key( $key, $group );
        $group = sanitize_key( $group );

        if ( $this->object_cache_enabled ) {
            return wp_cache_get( $key, $group );
        }

        return get_transient( $key );
    }

    /**
     * Sets a cached value.
     *
     * @since NEXT
     *
     * @param string $key   Cache key.
     * @param mixed  $value Value to cache.
     * @param int    $ttl   Time-to-live in seconds. 0 means no expiration.
     * @param string $group Cache group (default: 'default').
     *
     * @return bool Whether the cache was successfully set.
     */
    public function set( string $key, mixed $value, int $ttl = 0, string $group = 'default' ): bool {
        $key   = $this->get_key( $key, $group );
        $group = sanitize_key( $group );

        if ( $this->object_cache_enabled ) {
            return wp_cache_set( $key, $value, $group, $ttl );
        }

        // Falls back to transient cache.
        $ttl = $ttl ?: DAY_IN_SECONDS;

        return set_transient( $key, $value, $ttl );
    }

    /**
     * Deletes a cached value.
     *
     * @since NEXT
     *
     * @param string $key   Cache key.
     * @param string $group Cache group (default: 'default').
     *
     * @return bool Whether the cache was successfully deleted.
     */
    public function delete( string $key, string $group = 'default' ): bool {
        $key   = $this->get_key( $key, $group );
        $group = sanitize_key( $group );

        if ( $this->object_cache_enabled ) {
            return wp_cache_delete( $key, $group );
        }

        return delete_transient( $key );
    }

    /**
     * Flushes all cache entries for a specific group or all groups.
     *
     * @since NEXT
     *
     * @param string|null $group Cache group to flush, or null for all.
     *
     * @return void
     */
    public function flush( ?string $group = null ): void {
        if ( $this->object_cache_enabled ) {
            if ( is_null( $group ) ) {
                wp_cache_flush();
                return;
            }

            if ( function_exists( 'wp_cache_delete_multiple' ) ) {
                wp_cache_delete_multiple( [], $group );
            }

            // No native group flush available — bail early.
            return;
        }

        global $wpdb;

        $transient_like = is_null( $group )
            ? '_transient_' . $this->prefix . '%'
            : '_transient_' . $this->prefix . $group . '_%';

        $timeout_like = is_null( $group )
            ? '_transient_timeout_' . $this->prefix . '%'
            : '_transient_timeout_' . $this->prefix . $group . '_%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                $transient_like,
                $timeout_like
            )
        );
    }

    /**
     * Builds the final cache key with prefix and group.
     *
     * @since NEXT
     *
     * @param string $key   Base key.
     * @param string $group Cache group.
     *
     * @return string Prefixed and grouped key.
     */
    private function get_key( string $key, string $group = 'default' ): string {
        return sanitize_key( $this->prefix . $group . '_' . $key );
    }

    /**
     * Builds a consistent hashed cache key from arbitrary data.
     *
     * @since NEXT
     *
     * @param mixed  $data  Data used to generate the key.
     * @param string $group Optional cache group.
     *
     * @return string Hashed cache key.
     */
    public function build_key( mixed $data, string $group = 'default' ): string {
        if ( is_array( $data ) || is_object( $data ) ) {
            $data = wp_json_encode( $data );
        }

        $hash = md5( (string) $data );

        return $this->get_key( $hash, $group );
    }
}

/**
 * Initializes the module.
 *
 * @since NEXT
 */
Cache::get_instance();
