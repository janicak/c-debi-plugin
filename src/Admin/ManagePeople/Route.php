<?php

namespace C_DEBI\Admin\ManagePeople;

use C_DEBI\Admin\Common;
use C_DEBI\Entities;

class Route extends Common\Route {

    public function data_to_scripts() {

        $res = ['data' => [], 'total' => 0];

        $people = Entities\Entities::get_posts_fields(
            [ 'post_type' => 'person', 'posts_per_page' => -1 ],
            [ 'ID', 'post_date', 'post_modified', 'post_title' ],
            [
                'person_first_name', 'person_last_name', 'person_middle_name', 'person_nickname',
                'person_first_name_normalized', 'person_last_name_normalized', 'person_middle_name_normalized', 'person_nickname_normalized',
                'person_current_placement', 'person_degree'
            ]
        );

        global $wpdb;
        $attached_people_ids = array_unique(array_reduce(
            $wpdb->get_results($wpdb->prepare(
            "
                SELECT meta_value 
                FROM $wpdb->postmeta
                WHERE (
                    meta_key NOT LIKE %s ESCAPE %s
                    AND (
                        meta_key LIKE %s
                        OR meta_key LIKE %s
                        OR meta_key LIKE %s
                        OR meta_key LIKE %s
                        OR meta_key LIKE %s
                        OR meta_key LIKE %s
                    )
                )  
            ", "/_%", "/",
                "award_participants_%_person",
                "data_project_people_%_person",
                "dataset_people_%_person",
                "protocol_authors_%_person",
                "publication_authors_%_person",
                "publication_editors_%_person"
            )),
            function($acc, $row) {
                array_push($acc, $row->meta_value); return $acc;
                },
            []));

        foreach($people as &$person){
            $person['unattached'] = !in_array($person['ID'], $attached_people_ids);
            $person['permalink'] = get_post_permalink($person['ID']);
            $person['edit_link'] = get_edit_post_link($person['ID']);
        }

        $res['data'] = $people;
        $res['total'] = count($people);

        return $res;

    }

    static function delete_people($args) {
        ["IDs" => $IDs] = $args;
        foreach($IDs as $ID) {
            $deleted = wp_delete_post($ID, true);
        }
        return ["deleted" => $IDs];
    }

    static function merge_people($args) {
        [ "from" => $from, "to" => $to ] = $args;
        global $wpdb;

        $updated = $wpdb->query($wpdb->prepare(
            "
                UPDATE $wpdb->postmeta
                SET meta_value = %d
                WHERE (
                    meta_key NOT LIKE %s ESCAPE %s
                    AND (
                        meta_key LIKE %s
                        OR meta_key LIKE %s
                        OR meta_key LIKE %s
                        OR meta_key LIKE %s
                        OR meta_key LIKE %s
                        OR meta_key LIKE %s
                    )
                    AND meta_value = %d
                )  
            ",
            $to,
                "/_%", "/",
                    "award_participants_%_person",
                    "data_project_people_%_person",
                    "dataset_people_%_person",
                    "protocol_authors_%_person",
                    "publication_authors_%_person",
                    "publication_editors_%_person",
                $from
        ));

        $deleted = wp_delete_post($from, true);

        $posts = Entities\People::get_person_entities( $to );

        $data = [ "deleted" => $from, "updated" => [ $to => $posts ]];

        return $data;

    }

    // N.b., Used by script
    static function get_person_entities($ID){
        return Entities\People::get_person_entities( $ID );
    }

    static function reset_test_data() {
        $data = [];
        $data["deleted"] = (self::remove_test_data())["deleted"];

        $person_1_ID = wp_insert_post([
            "post_title" => "Test A. Person",
            "post_type" => "person"
        ]);
        $updated = update_field("field_5eb095c4279a5", "Test", $person_1_ID);
        $updated = update_field("field_5eb095f2279a6", "A", $person_1_ID);
        $updated = update_field("field_5eb095fa279a7", "Person", $person_1_ID);

        $person_2_ID = wp_insert_post([
            "post_title" => "Test B. Person",
            "post_type" => "person"
        ]);
        $updated = update_field("field_5eb095c4279a5", "Test", $person_2_ID);
        $updated = update_field("field_5eb095f2279a6", "B", $person_2_ID);
        $updated = update_field("field_5eb095fa279a7", "Person", $person_2_ID);

        $award_ID = wp_insert_post([
            "post_title" => "Test Award",
            "post_type" => "award"
        ]);
        $updated = update_field("field_5678bc399be7f", [["person" => $person_1_ID]], $award_ID);

        $dataset_ID = wp_insert_post([
            "post_title" => "Test Dataset",
            "post_type" => "dataset"
        ]);
        $updated = update_field("field_5870088199d58", [["person" => $person_1_ID]], $dataset_ID);

        $data_project_ID = wp_insert_post([
            "post_title" => "Test Data Project",
            "post_type" => "data_project"
        ]);
        $updated = update_field("field_587fdc9348352", [["person" => $person_1_ID]], $data_project_ID);

        $publication_ID = wp_insert_post([
            "post_title" => "Test Publication",
            "post_type" => "publication"
        ]);
        $updated = update_field("field_575b0db2455da", [["person" => $person_1_ID]], $publication_ID);

        $updated = update_option("c_debi_test_posts", [$person_1_ID, $person_2_ID, $award_ID, $dataset_ID, $data_project_ID, $publication_ID ]);

        $person_1 = Entities\Entities::get_posts_fields(
            [ 'post_type' => 'person', 'p' => $person_1_ID ],
            [ 'ID', 'post_date', 'post_modified', 'post_title' ],
            [ 'person_name' ]
        );

        $person_2 = Entities\Entities::get_posts_fields(
            [ 'post_type' => 'person', 'p' => $person_2_ID ],
            [ 'ID', 'post_date', 'post_modified', 'post_title' ],
            [ 'person_name' ]
        );

        $data["created"] = [$person_1, $person_2];

        return $data;
    }

    static function remove_test_data() {
        $data = ["deleted" => []];
        $test_posts = get_option("c_debi_test_posts");
        if ($test_posts) {
            foreach($test_posts as $ID) {
                $deleted = wp_delete_post($ID, true);
            }
            $updated = update_option("c_debi_test_posts", []);
            $data["deleted"] = $test_posts;
        }
        return $data;
    }

}


