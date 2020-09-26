<?php

namespace C_DEBI\Entities;

class Entities {

    static function get_posts_fields( $wp_query_args, $wp_field_names, $acf_field_names ) {

        return array_map(
            function($post) use ($wp_field_names, $acf_field_names) {

                $wp_fields = array_reduce(
                    $wp_field_names,
                    function( $acc, $wp_field_name ) use ( $post ) {
                        $acc[ $wp_field_name ] = isset($post->{$wp_field_name}) ? $post->{$wp_field_name} : null;
                        return $acc;
                    },
                    []
                );

                $acf_fields = array_reduce(
                    $acf_field_names,
                    function($acc, $acf_field_name ) use ($post){
                        $acc[$acf_field_name] = get_field($acf_field_name, $post->ID);
                        return $acc;
                    },
                    []
                );

                return array_merge($wp_fields, $acf_fields);

            },
            (new \WP_Query( $wp_query_args ))->posts
        );

    }

}