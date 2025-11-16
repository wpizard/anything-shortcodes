<?php

defined( 'ABSPATH' ) || exit;

/**
 * Returns the Cache instance.
 *
 * @since NEXT
 *
 * @return \AnyS\Modules\Cache
 */
function anys_cache(): \AnyS\Modules\Cache {
    return \AnyS\Modules\Cache::get_instance();
}

/**
 * Retrieves a cached value.
 *
 * @since NEXT
 *
 * @param string $key   Cache key.
 * @param string $group Cache group (default: 'default').
 *
 * @return mixed Cached value or false if not found.
 */
function anys_cache_get( string $key, string $group = 'default' ): mixed {
    return anys_cache()->get( $key, $group );
}

/**
 * Stores a value in cache.
 *
 * @since NEXT
 *
 * @param string $key   Cache key.
 * @param mixed  $value Value to cache.
 * @param int    $ttl   Time to live in seconds (default 0 = 1 day).
 * @param string $group Cache group (default: 'default').
 *
 * @return bool True on success, false on failure.
 */
function anys_cache_set( string $key, mixed $value, int $ttl = 0, string $group = 'default' ): bool {
    return anys_cache()->set( $key, $value, $ttl, $group );
}

/**
 * Deletes a cached value.
 *
 * @since NEXT
 *
 * @param string $key   Cache key.
 * @param string $group Cache group (default: 'default').
 *
 * @return bool True on success, false on failure.
 */
function anys_cache_delete( string $key, string $group = 'default' ): bool {
    return anys_cache()->delete( $key, $group );
}

/**
 * Flushes all cache entries or a specific group.
 *
 * @since NEXT
 *
 * @param string|null $group Cache group or null for all.
 *
 * @return void
 */
function anys_cache_flush( ?string $group = null ): void {
    anys_cache()->flush( $group );
}

/**
 * Retrieves a cached value or computes and stores it if missing.
 *
 * @since NEXT
 *
 * @param string   $key      Cache key.
 * @param callable $callback Callback to compute value if missing.
 * @param int      $ttl      TTL in seconds (default 0 = 1 day).
 * @param string   $group    Cache group (default: 'default').
 *
 * @return mixed Cached or computed value.
 */
function anys_cache_remember( string $key, callable $callback, int $ttl = 0, string $group = 'default' ): mixed {
    return anys_cache()->get( $key, $group ) ?: anys_cache_set( $key, $callback(), $ttl, $group ) && anys_cache_get( $key, $group );
}

/**
 * Generates a consistent cache key from arbitrary data.
 *
 * Uses Cache module build_key() to ensure prefix, hashing, and group consistency.
 *
 * @since NEXT
 *
 * @param mixed  $data  Data used to generate the key.
 * @param string $group Cache group (default: 'default').
 *
 * @return string Unique hashed cache key.
 */
function anys_cache_key( mixed $data, string $group = 'default' ): string {
    return anys_cache()->build_key( $data, $group );
}
