<?php

namespace AnyS\Modules;

defined( 'ABSPATH' ) || exit;

use AnyS\Traits\Singleton;

/**
 * Query module.
 *
 * Handles cached queries for posts, terms, users, and custom SQL.
 * Integrates AnyS cache module for improved performance.
 *
 * @since NEXT
 */
final class Query {
    use Singleton;

    /**
     * Default cache group.
     *
     * @since NEXT
     *
     * @var string
     */
    protected string $cache_group = 'query';

    /**
     * Default TTL in seconds (12 hours).
     *
     * @since NEXT
     *
     * @var int
     */
    protected int $default_ttl = 43200;

    /**
     * Adds hooks.
     *
     * @since NEXT
     */
    protected function add_hooks(): void {
        // Filter for default TTL.
        add_filter( 'anys/query/default_ttl', [ $this, 'filter_default_ttl' ] );
    }

    /**
     * Returns filtered default TTL.
     *
     * @since NEXT
     *
     * @param int $ttl Current TTL.
     *
     * @return int Filtered TTL.
     */
    public function filter_default_ttl( int $ttl ): int {
        return (int) apply_filters( 'anys/query/default_ttl', $this->default_ttl );
    }

    /**
     * Builds a unique cache key based on type and payload.
     *
     * @since NEXT
     *
     * @param string $type Query type (posts, terms, users, sql, etc.).
     * @param mixed  $payload Data to include in the key.
     *
     * @return string Unique cache key.
     */
    protected function build_cache_key( string $type, mixed $payload ): string {
        $blog_id = get_current_blog_id();
        $hash    = md5( wp_json_encode( $payload ) );

        return "{$this->cache_group}_{$type}_{$blog_id}_{$hash}";
    }

    /**
     * Checks whether caching is enabled via filter.
     *
     * @since NEXT
     *
     * @param string $query_type Query type identifier.
     *
     * @return bool True if cache is enabled, false otherwise.
     */
    protected function cache_enabled( string $query_type = '' ): bool {
        return (bool) apply_filters( 'anys/query/cache_enabled', true, $query_type );
    }

    /**
     * Retrieves a value from cache.
     *
     * @since NEXT
     *
     * @param string $key Cache key.
     *
     * @return mixed Cached value or false if not found.
     */
    protected function get_cache( string $key ): mixed {
        return anys_cache_get( $key, $this->cache_group );
    }

    /**
     * Stores a value in cache.
     *
     * @since NEXT
     *
     * @param string   $key   Cache key.
     * @param mixed    $value Value to store.
     * @param int|null $ttl   Time-to-live in seconds.
     *
     * @return void
     */
    protected function set_cache( string $key, mixed $value, ?int $ttl = null ): void {
        $ttl = $ttl ?? $this->filter_default_ttl( $this->default_ttl );

        anys_cache_set( $key, $value, $ttl, $this->cache_group );
    }

    /**
     * Deletes cache by key.
     *
     * @since NEXT
     *
     * @param string $key Cache key to clear.
     *
     * @return void
     */
    public function clear_cache_by_key( string $key ): void {
        if ( empty( $key ) ) {
            return;
        }

        anys_cache_delete( $key, $this->cache_group );
    }

    /**
     * Clears all AnyS query cache for this group.
     *
     * @since NEXT
     *
     * @return void
     */
    public function clear_all_cache(): void {
        anys_cache_flush( $this->cache_group );
    }

    /**
     * Generic cached query wrapper.
     *
     * @since NEXT
     *
     * @param string   $query_type Query type identifier.
     * @param callable $callback   Callback to execute if cache miss.
     * @param int|null $ttl        Cache TTL in seconds or null for default.
     * @param mixed    $payload    Data used to generate the cache key.
     *
     * @return mixed Cached or computed result.
     */
    protected function cached_query( string $query_type, callable $callback, ?int $ttl = null, mixed $payload = null ): mixed {
        if ( ! $this->cache_enabled( $query_type ) || false === $ttl ) {
            return $callback();
        }

        $cache_key = $this->build_cache_key( $query_type, $payload ?? $callback );
        $cached    = $this->get_cache( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $result = $callback();

        $this->set_cache( $cache_key, $result, $ttl );

        return $result;
    }

    /**
     * Returns cached posts.
     *
     * @since NEXT
     *
     * @param array    $args WP_Query arguments.
     * @param int|null $ttl  TTL in seconds or null for default.
     *
     * @return array Array of WP_Post objects.
     */
    public function get_posts( array $args = [], ?int $ttl = null ): array {
        $query_type = 'posts';
        $args       = apply_filters( 'anys/query/args', $args, $query_type );

        do_action( 'anys/query/before', $query_type, $args );

        $posts = $this->cached_query(
            $query_type,
            fn() => ( new \WP_Query( $args ) )->posts,
            $ttl,
            $args
        );

        do_action( 'anys/query/after', $query_type, $args, $posts );

        return $posts;
    }

    /**
     * Returns cached terms.
     *
     * @since NEXT
     *
     * @param array    $args get_terms arguments.
     * @param int|null $ttl  TTL in seconds or null for default.
     *
     * @return array Array of WP_Term objects.
     */
    public function get_terms( array $args = [], ?int $ttl = null ): array {
        $query_type = 'terms';
        $args       = apply_filters( 'anys/query/args', $args, $query_type );

        do_action( 'anys/query/before', $query_type, $args );

        $terms = $this->cached_query(
            $query_type,
            fn() => get_terms( $args ),
            $ttl,
            $args
        );

        do_action( 'anys/query/after', $query_type, $args, $terms );

        return $terms;
    }

    /**
     * Returns cached users.
     *
     * @since NEXT
     *
     * @param array    $args get_users arguments.
     * @param int|null $ttl  TTL in seconds or null for default.
     *
     * @return array Array of WP_User objects.
     */
    public function get_users( array $args = [], ?int $ttl = null ): array {
        $query_type = 'users';
        $args       = apply_filters( 'anys/query/args', $args, $query_type );

        do_action( 'anys/query/before', $query_type, $args );

        $users = $this->cached_query(
            $query_type,
            fn() => get_users( $args ),
            $ttl,
            $args
        );

        do_action( 'anys/query/after', $query_type, $args, $users );

        return $users;
    }

    /**
     * Returns cached custom SQL results.
     *
     * @since NEXT
     *
     * @param string   $sql SQL query string.
     * @param int|null $ttl TTL in seconds or null for default.
     *
     * @return array Array of query results.
     */
    public function get_results( string $sql, ?int $ttl = null ): array {
        global $wpdb;

        $query_type = 'sql';
        $sql        = apply_filters( 'anys/query/args', $sql, $query_type );

        do_action( 'anys/query/before', $query_type, $sql );

        $results = $this->cached_query(
            $query_type,
            fn() => $wpdb->get_results( $sql ),
            $ttl,
            $sql
        );

        do_action( 'anys/query/after', $query_type, $sql, $results );

        return $results;
    }
}

/**
 * Initializes the Query module.
 *
 * @since NEXT
 */
Query::get_instance();
