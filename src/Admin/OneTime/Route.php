<?php

namespace C_DEBI\Admin\OneTime;

use C_DEBI\Admin\Common as Common;
use C_DEBI\Entities;
use C_DEBI\Search\Search;

use WP2Static\Crawler;

class Route extends Common\Route
{
    public static function exec_wp2static(){
        do_action('wp2static_process_queue');
        return ['message' => 'done'];
    }

    public static function process_wp2static_urls(){
        $crawler = new Crawler();
        $offending_url = 'https://wp-cdebi-2:444/wp-content/uploads/docs/AT26-18 JFR Cork Recovery Prospectus Full 2014.pdf';
        $result = $crawler->crawlURL($offending_url);
        return ['message' => 'done'];
    }

    public static function acf_field_term_sync(){
        $post = get_post(14754);

        $post_acf_fields = get_fields($post->ID);

        $post_acf_fields_info = array_reduce(
            acf_get_field_groups( ['post_type' => get_post_type($post->ID)] ),
            function($acc, $group_key){
                $acf_group_fields = acf_get_fields($group_key);
                return array_merge($acc, $acf_group_fields);
            },
            []);

        foreach ($post_acf_fields_info as $post_acf_field_info){
            ['type' => $field_type, 'name' => $field_name] = $post_acf_field_info;

            $field_value = isset($post_acf_fields[$field_name]) && $post_acf_fields[$field_name] ? $post_acf_fields[$field_name] : null;

            if ($field_value){

                if ($field_type === 'taxonomy' && $post_acf_field_info['save_terms']) {
                    $term_id = (get_term($field_value))->term_id;

                    wp_set_object_terms(
                        $post->ID,
                        [ $term_id ],
                        $post_acf_field_info['taxonomy'],
                        true
                    );

                } else if ($field_type === 'repeater' && is_array($field_value)) {

                    foreach($post_acf_field_info['sub_fields'] as $subfield_info){
                        ['type' => $subfield_type, 'name' => $subfield_name, 'save_terms' => $save_terms] = $subfield_info;

                        if ($subfield_type === 'taxonomy' && $save_terms){
                            $term_ids = [];

                            foreach ($field_value as $field_row){
                                $subfield_value = isset($field_row[$subfield_name]) && $field_row[$subfield_name] ? $field_row[$subfield_name] : null;

                                if ($subfield_value){
                                    $term_ids[] = (get_term($subfield_value))->term_id;
                                }
                            }

                            $term_ids = array_unique($term_ids);

                            if (count($term_ids)){

                                wp_set_object_terms(
                                    $post->ID,
                                    $term_ids,
                                    $subfield_info['taxonomy'],
                                    true
                                );
                            }
                        }
                    }
                }
            }
        }

        return ['message' => 'done'];
    }

    public static function update_bidirectional_fields() {
        $query = new \WP_Query(
            [
                "post_type" => "award",
                "posts_per_page" => -1,
            ]
        );

        foreach ($query->posts as $post){
            $award_publications = get_field('award_publications', $post->ID, false);
            update_field('award_publications', $award_publications, $post->ID);
            $award_data_projects = get_field('award_data_projects', $post->ID, false);
            update_field('award_data_projects', $award_data_projects, $post->ID);
        }

        return ['message' => 'done'];
    }

    public static function process_people_names(){
        $query = new \WP_Query(
            [
                "post_type" => "person",
                "posts_per_page" => -1,
            ]
        );
        foreach ($query->posts as $post) {
            $name_field = get_field("field_567478d86825f", $post->ID);

            $first = $name_field[0]["first"];
            if ($first){
                $first_normalized = Entities\People::normalize_name($first);
                update_field("field_5eb095c4279a5", $first, $post->ID);
                update_field("field_5eb0966f279a9", $first_normalized, $post->ID);
            }

            $middle = $name_field[0]["middle"];
            if ($middle) {
                $middle_normalized = Entities\People::normalize_name( $middle );
                update_field( "field_5eb095f2279a6", $middle, $post->ID );
                update_field( "field_5eb0967f279aa", $middle_normalized, $post->ID );
            }

            $last = $name_field[0]["last"];
            if ($last) {
                $last_normalized = Entities\People::normalize_name( $last );
                update_field( "field_5eb095fa279a7", $last, $post->ID );
                update_field( "field_5eb09685279ab", $last_normalized, $post->ID );
            }

            $nickname = $name_field[0]["nickname"];
            if ($nickname) {
                $nickname_normalized = Entities\People::normalize_name( $nickname );
                update_field( "field_5eb0960e279a8", $nickname, $post->ID );
                update_field( "field_5eb0968a279ac", $nickname_normalized, $post->ID );
            }
        }

        return ["message" => "done"];
    }

    public static function dateToDate()
    {
        $results = [];
        $query = new \WP_Query(
            [
                "post_type" => "publication",
                "posts_per_page" => -1,
            ]
        );
        foreach ($query->posts as $post) {
            $published_date = get_field("field_56b1558d13c9b", $post->ID);
            if ($published_date) {
                $date = \DateTime::createFromFormat("n-j-Y", $published_date);
                $formatted_date = $date->format("Ymd");
                $updated = update_field("field_5e96430b4a66c", $formatted_date, $post->ID);
                $results[] = $updated;
            }
        }
        return [
            "count" => count($results),
            "results" => $results,
        ];
    }

    public static function resavePosts()
    {
        $results = [];
        $query = new \WP_Query(
            [
                "post_type" => ["data_project", "dataset"],
                "posts_per_page" => -1,
            ]
        );
        foreach ($query->posts as $post) {
            $post->post_title = $post->post_title . "";
            $results[] = wp_update_post($post);
        }
        return count($results);
    }

    public static function whatAreTheAwardRoles()
    {
        $awards_query = new \WP_Query(
            [
                "post_type" => "award",
                "posts_per_page" => -1,
            ]
        );

        $award_roles = array_reduce(
            $awards_query->posts, function ($acc, $a) {
                $award_people = get_field("award_participants", $a->ID);
                foreach ($award_people as $p) {
                    $acc[] = $p['role'];
                }
                return $acc;
            }, []
        );

        return array_unique($award_roles);
    }

    public static function moveDegreeCurrentPlacementToPeople()
    {
        $awards_query = new \WP_Query(
            [
                "post_type" => "award",
                "posts_per_page" => -1,
            ]
        );

        $field_names = ["current_placement", "degree"];

        // Create array of keys: post IDs, values: [current placement, degree]
        $people = array_reduce(
            $awards_query->posts, function ($acc, $a) use ($field_names) {
                $award_people = get_field("award_participants", $a->ID);
                foreach ($award_people as $p) {
                    $ID = $p['person']->ID;
                    if (!isset($acc[$ID])) {
                        foreach ($field_names as $f) {
                            $acc[$ID][$f] = null;
                        }
                    }
                    foreach ($field_names as $f) {
                        if ($p[$f]) {
                            if (!$acc[$ID][$f]) {
                                $acc[$ID][$f] = $p[$f];
                            } else {
                                if ($acc[$ID][$f] !== $p[$f]) {
                                    $acc[$ID][$f] .= ";" . $p[$f];
                                }
                            }
                        }
                    }
                }
                return $acc;
            }, []
        );

        foreach ($people as $ID => $fields) {
            foreach ($field_names as $f) {
                if ($fields[$f]) {
                    $updated = update_field('person_' . $f, $fields[$f], $ID);
                }
            }
        }

        return "done";
    }

}
