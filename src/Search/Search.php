<?php

namespace C_DEBI\Search;

use  C_DEBI\ThirdParty;

class Search {

    public function configure_search(){
        add_filter('query_vars', [$this, 'url_query_vars']);
    }

    const URL_QUERY_VARS_CONFIG = [
        'type' => [
            'post_obj_prop' => 'post_type',
        ],
        'publication_type' => [
            'taxonomy' => 'publication_type',
            'field' => 'slug'
        ],
        'award_type' => [
            'taxonomy' => 'award_type',
            'field' => 'slug'
        ],
        'instrument' => [
            'taxonomy' => 'instrument',
            'field' => 'term_id'
        ],
        'parameter' => [
            'taxonomy' => 'parameter',
            'field' => 'term_id'
        ],
        'category' => [
            'taxonomy' => 'category',
            'field' => 'slug'
        ],
        'organization' => [
            'taxonomy' => 'organization',
            'field' => 'slug'
        ],
        'person_id' => [
            'meta_keys' => [
                'publication_authors_$_person',
                'publication_editors_$_person',
                'award_participants_$_person',
                'data_project_people_$_person',
                'dataset_people_$_person',
                'protocol_authors_$_person'
            ]
        ]
    ];

    public function url_query_vars($vars){
        $add_query_vars = array_keys($this::URL_QUERY_VARS_CONFIG);
        return array_merge($vars, $add_query_vars);
    }
    
    static function get_args_from_url(){
        $search_query_args = [];

        foreach (self::URL_QUERY_VARS_CONFIG as $url_query_var => $url_query_var_config){
            $url_query_value = $_GET[$url_query_var] ?? false;

            $post_obj_prop = $url_query_var_config['post_obj_prop'] ?? null;
            $taxonomy = $url_query_var_config['taxonomy'] ?? null;
            $meta_keys = $url_query_var_config['meta_keys'] ?? null;

            if ($url_query_value){

                if ($post_obj_prop){
                    $search_query_args[$post_obj_prop] = $url_query_value;
                }

                if ($taxonomy){
                    $search_query_args['tax_query'] = $search_query_args['tax_query'] ?? [];
                    $search_query_args['tax_query'][] = [
                        'taxonomy' => $taxonomy,
                        'field' => $url_query_var_config['field'],
                        'terms' => $url_query_value
                    ];
                }

                if ($meta_keys){
                    $search_query_args['meta_query'] = $search_query_args['meta_query'] ?? ['relation' => 'OR'];
                    foreach ($meta_keys as $meta_key){
                        $search_query_args['meta_query'][] = [
                            'key' => $meta_key,
                            'compare' => '=',
                            'value' => intval($url_query_value)
                        ];
                        $search_query_args['suppress_filters'] = false;
                    }
                }
            }
        }

        $seach_text = $_GET['s'] ?? null;
        if ($seach_text){
            $search_query_args['s'] = $seach_text;
        }

        $search_query_args['posts_per_page'] = -1;

        $search_query_args['post_type'] = $search_query_args['post_type'] ?? ['post', 'award', 'dataset', 'protocol', 'publication', 'data_project', 'newsletter'];

        return $search_query_args;
    }

    static function cache_query($query_args){
        $query_response = null;

        if (is_plugin_active('pantheon-advanced-page-cache/pantheon-advanced-page-cache.php')){
            $query_response = ThirdParty\PantheonAdvancedPageCache::papcx_wp_query($query_args);

        } else {
            $query_response = self::query($query_args);
        }

        return $query_response;
    }

    static function query($query_args){
        $query_response = null;

        if (is_plugin_active('searchwp/index.php') && isset( $query_args['s'] )){
            $query_response = new \SWP_Query( $query_args );

        } else {
            $query_response = new \WP_Query( $query_args );
        }

        return $query_response;
    }

    static function url_query(){
        $query_args = self::get_args_from_url();

        return self::cache_query($query_args);
    }
}