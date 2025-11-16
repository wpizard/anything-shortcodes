<?php

defined( 'ABSPATH' ) || exit;

/**
 * Returns the Query instance.
 *
 * @since NEXT
 *
 * @return \AnyS\Modules\Query
 */
function anys_query(): \AnyS\Modules\Query {
    return \AnyS\Modules\Query::get_instance();
}

/**
 * Retrieves cached posts.
 *
 * @since NEXT
 *
 * @param array    $args WP_Query arguments.
 * @param int|bool|null $ttl TTL in seconds, or false to disable caching.
 *
 * @return array Array of WP_Post objects.
 */
function anys_get_posts( array $args = [], int|bool|null $ttl = null ): array {
    return anys_query()->get_posts( $args, $ttl );
}

/**
 * Retrieves cached terms.
 *
 * @since NEXT
 *
 * @param array    $args get_terms arguments.
 * @param int|bool|null $ttl TTL in seconds, or false to disable caching.
 *
 * @return array Array of WP_Term objects.
 */
function anys_get_terms( array $args = [], int|bool|null $ttl = null ): array {
    return anys_query()->get_terms( $args, $ttl );
}

/**
 * Retrieves cached users.
 *
 * @since NEXT
 *
 * @param array    $args get_users arguments.
 * @param int|bool|null $ttl TTL in seconds, or false to disable caching.
 *
 * @return array Array of WP_User objects.
 */
function anys_get_users( array $args = [], int|bool|null $ttl = null ): array {
    return anys_query()->get_users( $args, $ttl );
}

/**
 * Retrieves cached custom SQL results.
 *
 * @since NEXT
 *
 * @param string      $sql SQL query string.
 * @param int|bool|null $ttl TTL in seconds, or false to disable caching.
 *
 * @return array Array of query results.
 */
function anys_get_results( string $sql, int|bool|null $ttl = null ): array {
    return anys_query()->get_results( $sql, $ttl );
}

/**
 * Clears cache by a specific key.
 *
 * @since NEXT
 *
 * @param string $key Cache key.
 *
 * @return void
 */
function anys_clear_cache_by_key( string $key ): void {
    anys_query()->clear_cache_by_key( $key );
}

/**
 * Clears all AnyS query cache.
 *
 * @since NEXT
 *
 * @return void
 */
function anys_clear_all_cache(): void {
    anys_query()->clear_all_cache();
}
