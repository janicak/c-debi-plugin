<?php

namespace C_DEBI\ThirdParty;

class WP2Static {

    public function configure_wp2static() {

        if (is_plugin_active('wp2static/wp2static.php')) {
            add_filter('wp2static_detect_posts_pagination', '__return_false');
            add_filter('wp2static_detect_archives', '__return_false');
            add_filter('wp2static_detect_categories', '__return_false');
            add_filter('wp2static_detect_category_pagination', '__return_false');
            add_filter('wp2static_modify_initial_crawl_list', [$this, 'wp2static_modify_initial_crawl_list']);
        }
    }

    public function wp2static_modify_initial_crawl_list($url_queue){
        $exclude_starting_with = [
            '/content_block',
            '/eo_resource',
            '/mc4wp-form',
            '/newsletter',
            '/person',
            '/vc_grid_item',
        ];
        $included_urls = array_filter(array_unique($url_queue), function($url) use ($exclude_starting_with){
            foreach ($exclude_starting_with as $exclude_string){
                if (substr($url, 0, strlen($exclude_string)) === $exclude_string) {
                    return false;
                }
            }
            return true;
        });

        // Replace any backslashes with forward slashes
        $included_urls = array_map(function($url){
            return str_replace('\\', '/', $url);
        }, $included_urls);

        return $included_urls;
    }
}