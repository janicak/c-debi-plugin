<?php

namespace C_DEBI\ThirdParty;

use C_DEBI\Search\Search;

class PantheonAdvancedPageCache {

    public function configure_pantheon_cache() {

        if (is_plugin_active('pantheon-advanced-page-cache/pantheon-advanced-page-cache.php')){

            add_action( 'save_post', [$this, 'save_post']);
            add_filter( 'pantheon_wp_main_query_surrogate_keys', [$this, 'pantheon_wp_main_query_surrogate_keys']);

        }
    }

    public function save_post($post_id){
        pantheon_wp_clear_edge_keys( array( 'post-'.$post_id ) );
    }

    public function pantheon_wp_main_query_surrogate_keys( $keys ) {

        if ( is_search() ) {
            $query_args = Search::get_args_from_url();
            $query = Search::query($query_args);

            foreach( $query->posts as $post ) {
                $keys[] = 'post-' . $post->ID;
            }
        }

        return $keys;
    }
    
    static function papcx_wp_query( $query_args ) {
        $cache_key = md5( serialize( $query_args ) );
        $cache_value = wp_cache_get( $cache_key, 'papc-non-persistent' );

        if ( $cache_value !== false ) {
            return $cache_value;
        }

        $query = Search::query($query_args);

        wp_cache_set( $cache_key, $query, 'papc-non-persistent' );

        return $query;
    }


}