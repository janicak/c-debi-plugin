<?php

namespace C_DEBI\Admin\BcoDmoSync;

use C_DEBI\Entities;
use C_DEBI\Utilities;
use C_DEBI\ThirdParty;
use GuzzleHttp;

class BCODMO {

    private const BCODMO_API_BASE_URL = 'https://api.bco-dmo.org/v1/C-DEBI/';

    private $status_percentage = 0;
    
    public function init_update($trigger_sync, $find_unlinked_people, $use_cache = true){

        $res = [];

        $this->status_percentage = 0;
        $this->update_process_status("Sync initiated");

        if ($trigger_sync) {
            ['datasets' => $datasets, 'projects' => $data_projects ] = $this->fetch_data($use_cache);
            $this->status_percentage = 30;

            $dataset_stats = $this->update_posts($datasets, 'dataset');
            $this->status_percentage = 80;

            $data_project_stats = $this->update_posts($data_projects, 'data_project');
            $this->status_percentage = 95;

            $res = [ 'datasets' => $dataset_stats, 'data_projects' => $data_project_stats ];
        }

        if ($find_unlinked_people){
            $this->status_percentage = 95;

            $res['unlinked_people'] = $this->find_unlinked_people();
        }

        $this->status_percentage = 0;
        $this->update_process_status("Sync initiated");

        return $res;

    }

    private function update_process_status($message){
        update_option("bco-dmo_process_status", ["statusMessage" => $message, "statusPercentage" => $this->status_percentage]);
    }

    static function get_process_status(){
        return get_option("bco-dmo_process_status");
    }
    
    public function fetch_data($use_cache = false){
        $api_responses = [];

        $file_cache_path = Utilities\PluginMeta::plugin_uploads_path() . 'bco-dmo.json';

        if ($use_cache){ //Use cached data
            $api_responses = json_decode(file_get_contents($file_cache_path), true);

        } else { //Fetch data
            $api_endpoints = [
                'datasets', 'datasets/instruments', 'datasets/parameters',
                'datasets/people', 'projects', 'projects/people'
            ];

            foreach($api_endpoints as $i => $api_endpoint){
                $this->status_percentage = $i * 5;
                $this->update_process_status("Querying BCO-DMO");
                $api_responses[$api_endpoint] = $this->bcodmo_api_query($api_endpoint);
            }

            // Cache data
            $file_cache = fopen( $file_cache_path, 'w' );
            fwrite( $file_cache, json_encode( $api_responses ) );
            fclose( $file_cache );
        }

        return $this->normalize_and_link_query_data($api_responses);
    }

    private function bcodmo_api_query( $endpoint ){
        $req_url = self::BCODMO_API_BASE_URL . $endpoint;

        $res = ( new GuzzleHttp\Client() )->request( 'GET', $req_url, ['verify' => false] );

        if ($res->getStatusCode() === 200) {
            $res_body = Utilities\Helpers::parseHttpResponseBody($res->getBody());

            if ($res_body["results"]["bindings"]){
                return $res_body["results"]["bindings"];

            } else {
                wp_send_json_error("Error: malformed API response");
            }

        } else {
            wp_send_json_error("Error, status code: " . $res->getStatusCode());
        }

        return null;
    }

    public function normalize_and_link_query_data($api_responses){
        [   'datasets' => $datasets, 'datasets/instruments' => $datasets_instruments,
            'datasets/parameters' => $datasets_parameters, 'datasets/people' => $datasets_people,
            'projects' => $projects, 'projects/people' => $projects_people
        ] = $api_responses;

        // Restructure response data fields from $key => [ "value" => $value, ... ] to $key => $value
        foreach([
            &$datasets, &$datasets_instruments, &$datasets_parameters, &$datasets_people, &$projects, &$projects_people
        ] as &$entities){
            foreach($entities as &$entity){
                foreach($entity as $field => $field_info){
                    $entity[$field] = $field_info["value"];
                }
            }
        }

        // Organize projects by key
        $projects = array_reduce($projects, function($acc, $project) {
            $project_key = $project['project'];
            $acc[$project_key] = $project;
            return $acc;
        }, []);

        // Link datasets to projects
        foreach ($datasets as $dataset) {
            $project_id = $dataset['project'];
            if (!isset($projects[$project_id]['datasets'])){
                $projects[$project_id]['datasets'] = [];
            }
            $projects[$project_id]['datasets'][] = $dataset;
        }

        // Organize unique datasets (API provides duplicate datasets when linked to multiple projects) by key
        $datasets = array_reduce($datasets, function($acc, $dataset) {
            $dataset_key = $dataset['dataset'];
            $acc[$dataset_key] = $dataset;
            return $acc;
        }, []);

        // Link child entities (instruments, parameters, people) to parents (dataset, projects)
        foreach([
            [
                "parent_entities" => &$datasets, "parents_child_field" => "instruments",
                "child_entities" => $datasets_instruments, "childs_parent_key_field" => "dataset"
            ],
            [
                "parent_entities" => &$datasets, "parents_child_field" => "parameters",
                "child_entities" => $datasets_parameters, "childs_parent_key_field" => "dataset"
            ],
            [
                "parent_entities" => &$datasets, "parents_child_field" => "people",
                "child_entities" => $datasets_people, "childs_parent_key_field" => "dataset"
            ],
            [
                "parent_entities" => &$projects, "parents_child_field" => "people",
                "child_entities" => $projects_people, "childs_parent_key_field" => "project"
            ],
        ] as $entity_linking_config) {
            [
                "parent_entities" => &$parent_entities,
                "parents_child_field" => $parents_child_field,
                "child_entities" => $child_entities,
                "childs_parent_key_field" => $childs_parent_key_field
            ] = $entity_linking_config;

            foreach($child_entities as $child){
                $parent_key = $child[$childs_parent_key_field];

                if (!isset($parent_entities[$parent_key][$parents_child_field])){
                    $parent_entities[$parent_key][$parents_child_field] = [];
                }

                $parent_entities[$parent_key][$parents_child_field][] = $child;
            }
        }

        return [ "datasets" => $datasets, "projects" => $projects ];
    }

    public function update_posts($source_entities, $target_type){
        [   "source_title_field" => $source_title_field,
            "source_id_field" => $source_id_field,
            "target_id_field" => $target_id_field,
            "target_id_field_key" => $target_id_field_key,
            "field_mapping" => $field_mappings,
            "plural_label" => $plural_label,
            "status_percentage_increment" => $status_percentage_increment,
        ] = include(dirname( __FILE__ ) . '/bcodmo-'. $target_type. '-config.php');

        $target_posts_stats = ["created" => 0, "updated" => 0];
        
        $entities_total = count($source_entities);
        $entities_cursor = 0;

        foreach ($source_entities as $source_entity){
            $entities_cursor++;
            
            $this->status_percentage += $status_percentage_increment;
            $this->update_process_status("Updating " . $entities_cursor . ' of ' .  $entities_total . " " . $plural_label);

            $target_post = self::get_or_create_post_with_meta_value(
                $target_type, $target_id_field, $source_entity[$source_id_field],
                $source_entity[$source_title_field], $target_id_field_key
            );

            $target_post = $this->compare_and_update_post_fields($source_entity, $field_mappings, $target_post);

            ThirdParty\ACF::acf_field_term_sync($target_post);

            $target_posts_stats['created'] += $target_post->new ? 1 : 0;
            $target_posts_stats['updated'] += !$target_post->new ? 1 : 0;
        }

        return $target_posts_stats;
    }

    private function compare_and_update_post_fields( $source_data, $field_mappings, $post){

        foreach ( $field_mappings as $field_mapping) {
            [
                "source_field_name" => $source_field_name,
                "post_field_name" => $post_field_name,
                "post_field_type" => $post_field_type,
            ] = $field_mapping;

            $acf_field_key = isset($field_mapping["acf_field_key"]) ? $field_mapping["acf_field_key"] : null;

            switch( $post_field_type ) {
                case "repeater":
                    $subfield_mappings = $field_mapping["subfield_mappings"];

                    if (isset($source_data[$source_field_name])){
                        $should_update_field = false;

                        if (isset($post->fields[$post_field_name]) & is_array($post->fields[$post_field_name])){
                            if (count($post->fields[$post_field_name]) !== count($source_data[$source_field_name])){
                                $should_update_field = true;
                            }
                        } else {
                            $should_update_field = true;
                        }

                        $new_post_field_rows_value = [];

                        foreach($source_data[$source_field_name] as $source_field_row_i => $source_field_row)  {
                            $new_post_field_row_value = [];

                            foreach ($subfield_mappings as $subfield_mapping){
                                [
                                    "source_field_name" => $source_subfield_name,
                                    "post_field_name" => $post_subfield_name,
                                ] = $subfield_mapping;
                                
                                $old_post_subfield_value = null;
                                // If the repeater field is on the post...
                                if (isset( $post->fields[$post_field_name] )){
                                    // ... and if the field has the same numbered row ...
                                    if (isset($post->fields[$post_field_name][$source_field_row_i])){ 
                                        // ... and if the subfield is on the row and has a truthy value...
                                        if ( isset($post->fields[$post_field_name][$source_field_row_i][$post_subfield_name])
                                            && $post->fields[$post_field_name][$source_field_row_i][$post_subfield_name] ){
                                            // ... set $old_post_subfield_data with the value
                                            $old_post_subfield_value = $post->fields[$post_field_name][$source_field_row_i][$post_subfield_name];
                                        }
                                    }
                                }

                                $new_post_subfield_value = null;
                                if (isset( $source_field_row[ $source_subfield_name ] )){
                                    if (isset($subfield_mapping["field_value_callback"])){
                                        $new_post_subfield_value = $subfield_mapping["field_value_callback"](
                                            $source_field_row, $source_data, $source_field_row_i, $post
                                        );
                                    } else {
                                        $new_post_subfield_value = $source_field_row[ $source_subfield_name ];
                                    }
                                }
                                
                                $new_post_field_row_value[$post_subfield_name] = $new_post_subfield_value;

                                // If the old and new subfield data are different, mark the post field for updating
                                if ( $this->field_data_different( $new_post_subfield_value, $old_post_subfield_value ) ){
                                    $should_update_field = true;
                                }
                            }

                            $new_post_field_rows_value[] = $new_post_field_row_value;
                        }

                        if ($should_update_field){
                            //delete_field( $acf_field_key, $post->ID );
                            update_field( $acf_field_key, $new_post_field_rows_value, $post->ID );
                        }

                    } else {
                        // If a field value on the post is not/no longer present in the source data, delete it
                        if (isset($post->fields[$post_field_name]) && $post->fields[$post_field_name]){
                            delete_field( $acf_field_key, $post->ID );
                        }
                    }
                    break;

                default: // Field is not a repeater field

                    $new_post_field_value = null;

                    if (isset($source_data[ $source_field_name ])){
                        $new_post_field_value = isset( $field_mapping[ "field_value_callback" ] )
                            ? $field_mapping[ "field_value_callback" ]( $source_data )
                            : $source_data[ $source_field_name ];
                    }

                    switch($post_field_type) {
                        case "post_title":

                            $old_post_field_value = $post->post_title;

                            if ( $this->field_data_different( $new_post_field_value, $old_post_field_value) ) {
                                wp_update_post(['ID' => $post->ID, 'post_title' => $new_post_field_value ] );
                            }

                            break;

                        default:

                            $old_post_field_value = null;

                            if (isset( $post->fields[ $post_field_name ] ) && $post->fields[ $post_field_name ]){
                                $old_post_field_value = $post->fields[ $post_field_name ];
                            }

                            if ( $this->field_data_different( $new_post_field_value, $old_post_field_value) ) {
                                if ($new_post_field_value) {
                                    update_field( $acf_field_key, $new_post_field_value, $post->ID );
                                } else {
                                    delete_field( $acf_field_key, $post->ID );
                                }

                            }
                            break;
                    }

            }
        }

        return $post;
    }

    private function field_data_different($a, $b){
        if (!$a && !$b){
            return false;
        }
        return $a !== $b;
    }

    static function find_unlinked_people(){
        $unlinked_people = [];

        $parent_entity_posts = (new \WP_Query(["post_type" => ["dataset", "data_project"], "posts_per_page" => -1]))->posts;

        $linked_people = [];
        $parent_post_type_config = [
            "dataset" => [
                "people_repeater_field_name" => 'dataset_people',
                "people_name_text_subfield" => 'name',
                "people_person_post_subfield" => 'person'
            ],
            "data_project" => [
                "people_repeater_field_name" => 'data_project_people',
                "people_name_text_subfield" => 'name',
                "people_person_post_subfield" => 'person'
            ]
        ];

        foreach ($parent_entity_posts as $parent_entity_post){
            [
                "people_repeater_field_name" => $people_repeater_field_name,
                "people_name_text_subfield" => $people_name_text_subfield,
                "people_person_post_subfield" => $people_person_post_subfield
            ] = $parent_post_type_config[$parent_entity_post->post_type];

            $people_repeater_field_rows = get_field( $people_repeater_field_name, $parent_entity_post->ID );

            foreach ($people_repeater_field_rows as $i => $row){
                $full_name_text = $row[$people_name_text_subfield];
                $person_post = $row[$people_person_post_subfield];

                if ($full_name_text && $person_post){

                    if (!isset($linked_people[$full_name_text])){
                        $linked_people[$full_name_text] = $person_post;
                    }
                }

                if ($full_name_text && !$person_post){

                    if (!isset($unlinked_people[$full_name_text])){
                        $parsed_name = self::parse_bco_dmo_name($full_name_text);

                        $matched_person_posts = array_map(
                            function($entity) {
                                return [
                                    'post_title' => $entity->post_title,
                                    'ID' => $entity->ID
                                ];
                            },
                            Entities\People::get_people_by_first_and_last_name(
                                $parsed_name['first'], $parsed_name['last']
                            )
                        );

                        $unlinked_people[$full_name_text] = [
                            "matched_person_posts" => $matched_person_posts,
                            "parsed_name" => $parsed_name,
                            "instances" => []
                        ];
                    }

                    $unlinked_people[$full_name_text]["instances"][] = [
                        "post_id" => $parent_entity_post->ID,
                        "field_name" => $people_repeater_field_name,
                        "row" => $i + 1
                    ];
                }
            }
        }

        foreach($unlinked_people as $full_name_text => $unlinked_person_info) {

            // If identical person text is linked to a person entity on a different post, link the text and person entity on this post
            if (isset($linked_people[$full_name_text])){
                $person_post = $linked_people[$full_name_text];

                foreach ($unlinked_person_info['instances'] as $instance){
                    ["post_id" => $post_id, "field_name" => $field_name, "row" => $row ] = $instance;
                    update_sub_field([$field_name, $row, "person"], $person_post->ID, $post_id);
                }

                unset($unlinked_people[$full_name_text]);
            }
        }

        return $unlinked_people;

    }

    public function link_names_to_people( $people){
        $link_stats = ["rows_updated" => 0, "people_created" => 0];

        foreach ($people as $person_info) {
            [
                "instances" => $parent_post_instances, "selected_entity" => $person_post_id,
                "create_new" => $create_new_person, "parsed_name" => $parsed_name
            ] = $person_info;

            if ($create_new_person === 'true') {
                ['post_title' => $person_post_title, 'first' => $first, 'last' => $last] = $parsed_name;
                $person_post_id = (
                    Entities\People::create_person(['first' => $first, 'last' => $last], $person_post_title)
                )['id'];
                $link_stats["people_created"] += 1;
            }

            foreach ($parent_post_instances as $parent_post_instance) {
                ['post_id' => $post_id, 'field_name' => $field_name, 'row' => $row] = $parent_post_instance;
                update_sub_field([$field_name, $row, "person"], $person_post_id, $post_id);
                $link_stats["rows_updated"] += 1;
            }
        }
        return $link_stats;
    }

    static function get_or_create_post_with_meta_value( $post_type, $id_key, $id_value, $post_title = null, $id_acf_field_key = null){
        $post = null;

        $query = new \WP_Query([
            'post_type' => $post_type,
            'meta_key' => $id_key,
            'meta_value' => $id_value
        ]);

        if ($query->posts){
            $post = $query->posts[0];
            $post->fields = get_fields($post->ID);
            $post->new = false;

        } else if ($post_title && $id_acf_field_key) {
            $post_id = wp_insert_post( [
                'post_title' => $post_title,
                'post_type' => $post_type,
                'post_status' => 'publish'
            ] );
            update_field($id_acf_field_key, $id_value, $post_id);
            $post = get_post($post_id);
            $post->fields = get_fields($post_id);
            $post->new = true;
        }

        return $post;
    }

    static function get_or_create_term_with_name_and_taxonomy( $name, $taxonomy){
        $term = get_term_by( 'name', $name, $taxonomy );

        $term_id = $term
            ? $term->term_id
            : (wp_insert_term( $name, $taxonomy ))->term_id;

        return $term_id;
    }

    static function parse_bco_dmo_name( $name){
        $parser = new Utilities\FullNameParser();

        ['fname' => $first_name, 'lname' => $last_name, 'initials' => $initials] = $parser->parse_name($name);

        $formatted_name = $first_name;

        if ($initials){
            $initials = str_replace(".", "", $initials);
            $initials = explode(" ", $initials);
            $initials = count($initials) === 1
                ? $initials[0] . '.'
                : implode(". ", $initials);

            $formatted_name .= " " . $initials;
        }

        $formatted_name .= " " . $last_name;

        return [
            "post_title" => $formatted_name,
            "first" => $first_name,
            "last" => $last_name
        ];
    }

}