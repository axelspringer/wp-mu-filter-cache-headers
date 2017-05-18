<?php
defined('ABSPATH') || exit;
getenv('WP_LAYER') || exit;

// is this is not frontend
if (strtolower(getenv('WP_LAYER')) !== 'frontend') {
	return;
}

function asse_filter_cache_headers($headers, WP $wp) {

  $defaults = [
    'maxAge'                => 5 * 60,
    'staleWhileRevalidate'  => 3600 * 24,
    'staleIfError'          => 3600 * 24 * 3
  ];

  // this is for now
  $config = $defaults;

  // http://www.sobstel.org/blog/http-cache-stale-while-revalidate-stale-if-error/
  $headers['Cache-Control'] = "max-age={$config['maxAge']}, stale-while-revalidate={$config['staleWhileRevalidate']}, stale-if-error={$config['staleIfError']}";

  return $headers;
}

add_filter('wp_headers', 'asse_filter_cache_headers', 1, 2);

