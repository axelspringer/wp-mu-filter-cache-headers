<?php
defined( 'ABSPATH' ) || exit;

class Asse_Cache_Control {

    const WP_CACHES = [
        'front_page'   => [
            'max-age'  => 300,           //                5 min
            's-maxage' => 150,            //                2 min 30 sec
            'public'   => true,
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'single'      => [
            'max-age'  => 600,           //               10 min
            's-maxage' => 60,            //                1 min
            'mmulti'   => 1,              // enabled,
            'public'   => true,
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'page'        => [
            'max-age'  => 1200,          //               20 min
            's-maxage' => 300,            //                5 min
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'home'         => [
            'max-age'  => 180,           //                3 min
            's-maxage' => 45,            //                      45 sec
            'paged'    => 5,              //                       5 sec
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'category'   => [
            'max-age'  => 900,           //               15 min
            's-maxage' => 300,           //                5 min
            'paged'    => 8,              //                       8 sec
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'tag'         => [
            'max-age'  => 900,           //               15 min
            's-maxage' => 300,           //                5 min            //                       8 sec
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'author'      => [
            'max-age'  => 1800,          //               30 min
            's-maxage' => 600,           //               10 min
            'paged'    => 10,             //                      10 sec
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'date'        =>  [
            'max-age'  => 10800,         //      3 hours
            's-maxage' => 2700,          //               45 min
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'feed'        => [
            'max-age'  => 5400,          //       1 hours 30 min
            's-maxage' => 600,            //               10 min
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'attachment'   => [
            'max-age'  => 10800,         //       3 hours
            's-maxage' => 2700,          //               45 min
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        'search'       => [
            'max-age'  => 1800,          //               30 min
            's-maxage' => 600,            //               10 min
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ],
        '404'     => [
            'max-age'  => 900,           //               15 min
            's-maxage' => 300,            //                5 min
            'stale-while-revalidate' => 3600 * 24,
            'stale-if-error' => 3600 * 24 * 3
        ]
    ];

    public function __construct() {
        add_action( 'template_redirect', array( $this, 'send_cache_control_headers' ), 0 );
    }

    public function send_http_header( $directives ) {
        if ( ! empty( $directives ) ) {
            header ( 'Cache-Control: ' . $directives , true );
        }
    }

    public function cache_control_directives() {
        global $wp_query;

        $directives = null;

        if ( ! $this->should_be_cached() ) {
            $directives = get_cache_control_directive();
        }

        if ( $wp_query->is_front_page() && ! is_paged() ) {
            $directives = $this->get_cache_control_directive( 'front_page' );
        } elseif ( $wp_query->is_single() ) {
            $directives = $this->get_cache_control_directive( 'single' );
        } elseif ( $wp_query->is_page() ) {
            $directives = $this->get_cache_control_directive( 'page' );
        } elseif ( $wp_query->is_home() ) {
            $directives = $this->get_cache_control_directive( 'home' );
        } elseif ( $wp_query->is_category() ) {
            $directives = $this->get_cache_control_directive( 'category' );
        } elseif ( $wp_query->is_tag() ) {
            $directives = $this->get_cache_control_directive( 'tag' );
        } elseif ( $wp_query->is_author() ) {
            $directives = $this->get_cache_control_directive( 'author' );
        } elseif ( $wp_query->is_attachment() ) {
            $directives = $this->get_cache_control_directive( 'attachement' );
        } elseif ( $wp_query->is_search() ) {
            $directives = $this->get_cache_control_directive( 'search' );
        } elseif ( $wp_query->is_404() ) {
            $directives = $this->get_cache_control_directive( '404' );
        } elseif ( $wp_query->is_date() ) {
            if ( ( is_year() && strcmp(get_the_time('Y'), date('Y')) < 0 ) ||
             ( is_month() && strcmp(get_the_time('Y-m'), date('Y-m')) < 0 ) ||
             ( ( is_day() || is_time() ) && strcmp(get_the_time('Y-m-d'), date('Y-m-d')) < 0 ) ) {
                $directives = $this->get_cache_control_directive( 'date' );
            } else {
                $directives = $this->get_cache_control_directive( 'home' );
            }
        }

        return apply_filters( 'asse_cache_control_directives', $directives);
    }

    public function get_cache_control_directive( $default ) {
        $spec = [
            'max-age',
            's-maxage',
            'min-fresh',
            'must-revalidate',
            'no-cache',
            'no-store',
            'no-transform',
            'public',
            'private',
            'proxy-revalidate',
            'stale-while-revalidate',
            'stale-if-error'
        ];

        if ( empty( $default ) || ! array_key_exists( $default, self::WP_CACHES ) ) {
            return 'no-cache, no-store, must-revalidate';
        }

        $default = array_intersect_key( self::WP_CACHES[ $default ], array_flip( $spec ) );
        $directives = [];

        foreach( $default as $key => $value ) {
            $directives[] = is_bool( $value ) ? $key : $key . '=' . $value;
        }

        return implode( ', ', $directives );
    }

    public function should_be_cached() {
        return ! ( is_preview() || is_user_logged_in() || is_trackback() || is_admin() );
    }

    public function send_cache_control_headers() {
        $this->send_http_header($this->cache_control_directives());
    }

}

$asse_cache_control = new Asse_Cache_Control();
