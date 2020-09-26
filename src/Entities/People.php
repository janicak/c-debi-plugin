<?php

namespace C_DEBI\Entities;

class People {

    public function configure_people_hooks(){
        add_action('acf/save_post', [$this, 'acf_save_post']);
    }

    public function acf_save_post($post_id){
        remove_action('acf/save_post', [$this, 'acf_save_post']);

        self::normalize_name_fields($post_id);

        add_action('acf/save_post', [$this, 'acf_save_post']);
    }

    static function get_people_by_first_and_last_name($first, $last) {
        $first_normalized = self::normalize_name($first);
        $last_normalized = self::normalize_name($last);

        $query = new \WP_query([ 'post_type' => 'person', 'meta_query' => [
            'relation' => 'AND',
            [ 'key' => 'person_last_name_normalized', 'value' => $last_normalized ],
            [ 'key' => 'person_first_name_normalized', 'value' => $first_normalized ]
        ]]);

        if (!count($query->posts)) {
            return People::lastNameMetaQuery($last);
        } else {
            return $query->posts;
        }
    }

    static function lastNameMetaQuery($last) {
        $last_normalized = self::normalize_name($last);
        $query = new \WP_query([ 'post_type' => 'person', 'meta_query' => [
            'relation' => 'AND',
            [ 'key' => 'person_last_name_normalized', 'value' => $last_normalized ],
        ]]);

        return $query->posts;
    }

    static function get_person_entities( $ID ) {

        $response = [];

        $query = new \WP_Query( [
            "post_type" => [ 'publication', 'award', 'data_project', 'dataset', 'protocol' ],
            "posts_per_page" => -1,
            "meta_query" => [
                'relation' => 'OR',
                [
                    'key' => 'publication_authors_$_person',
                    'compare' => '=',
                    'value' => $ID
                ],
                [
                    'key' => 'publication_editors_$_person',
                    'compare' => '=',
                    'value' => $ID
                ],
                [
                    'key' => 'award_participants_$_person',
                    'compare' => '=',
                    'value' => $ID
                ],
                [
                    'key' => 'data_project_people_$_person',
                    'compare' => '=',
                    'value' => $ID
                ],
                [
                    'key' => 'dataset_people_$_person',
                    'compare' => '=',
                    'value' => $ID
                ],
                [
                    'key' => 'protocol_authors_$_person',
                    'compare' => '=',
                    'value' => $ID
                ],
            ]
        ] );

        $post_types = get_post_types( [], 'objects' );

        $posts_by_type = array_reduce( $query->posts, function( $acc, $post ) use ( $post_types ) {

            if( !isset( $acc[ $post->post_type ] ) ) {
                $acc[ $post->post_type ] = [ 'label' => $post_types[ $post->post_type ]->label, 'posts' => [] ];
            }
            $acc[ $post->post_type ][ 'posts' ][] = [
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'permalink' => get_post_permalink( $post->ID ),
                'edit_link' => get_edit_post_link( $post->ID )
            ];

            return $acc;
        }, [] );

        foreach( $posts_by_type as $type => $v ) {
            $response[] = $v;
        }

        return $response;

    }

    static function create_person( $fields, $post_title){
        ["first" => $first, "last" => $last] = $fields;
        $middle = isset($fields["middle"]) ? $fields["middle"] : null;
        $nickname = isset($fields["nickname"]) ? $fields["nickname"] : null;

        $post_id = \wp_insert_post([
                'post_type' => 'person',
                'post_title' => $post_title,
                'post_status' => 'publish']
        );
        if ($post_id) {
            update_field('field_5eb095c4279a5', $first, $post_id);
            update_field('field_5eb095fa279a7', $last, $post_id);
            if ($middle) {
                update_field('field_5eb095f2279a6', $middle, $post_id);
            }
            if ($nickname) {
                update_field('field_5eb0960e279a8', $middle, $post_id);
            }
            self::normalize_name_fields($post_id);
            return ["id" => $post_id, "text" => $post_title . " [ID:" . $post_id . "]"];
        } else {
            return ["error" => "unable to insert post"];
        }
    }

    static function normalize_name_fields($post_id){
        $config = [
            [
                "field_name" => "person_first_name",
                "normalized_field_name" => "person_first_name_normalized",
                "normalized_field_key" => "field_5eb0966f279a9"
            ],
            [
                "field_name" => "person_middle_name",
                "normalized_field_name" => "person_middle_name_normalized",
                "normalized_field_key" => "field_5eb0967f279aa"
            ],
            [
                "field_name" => "person_last_name",
                "normalized_field_name" => "person_last_name_normalized",
                "normalized_field_key" => "field_5eb09685279ab"
            ],
            [
                "field_name" => "person_nickname",
                "normalized_field_name" => "person_nickname_normalized",
                "normalized_field_key" => "field_5eb0968a279ac"
            ]
        ];

        $fields = get_fields($post_id);

        foreach ($config as $c){
            [
                "field_name" => $field_name,
                "normalized_field_name" => $normalized_field_name,
                "normalized_field_key" => $normalized_field_key
            ] = $c;

            $field_value = isset($fields[$field_name]) ? $fields[$field_name] : '';

            if ($field_value){
                $old_normalized_field_value = isset($fields[$normalized_field_name]) ? $fields[$normalized_field_name] : '';
                $new_normalized_field_value = self::normalize_name($field_value);

                if ( $old_normalized_field_value !== $new_normalized_field_value ) {
                    update_field($normalized_field_key, $new_normalized_field_value, $post_id);
                }
            }
        }
    }

    static function normalize_name($name){
        $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        $str = strtr( $name, $unwanted_array );
        return strtolower(preg_replace("/[^a-zA-Z0-9 ]/", "", $str));
    }

}