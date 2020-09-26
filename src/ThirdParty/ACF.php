<?php

namespace C_DEBI\ThirdParty;

class ACF {

    private $acf_json_dir;

    public function __construct(){
        $this->acf_json_dir = dirname(__FILE__) . '/acf-json';
    }

    public function configure_acf(){
        add_action( 'acf/init', [$this, 'acf_init'] );

        add_filter( 'acf/fields/post_object/result', [$this, 'acf_post_object_result'], 10, 4 );

        add_filter( 'acf/fields/relationship/query', [$this, 'searchwp_acf_relationship_field_search'], 90, 3 );

        add_filter( 'acf/fields/relationship/result', [$this, 'acf_relationship_result'], 10, 4 );

        add_action( 'acf/save_post', [$this, 'acf_save_post'] );

        add_filter( 'acf/settings/load_json', [$this, 'set_acf_json_load_point'] );

        add_filter( 'acf/settings/save_json', [$this, 'set_acf_json_save_point'] );

        add_filter( 'acf/update_value', [$this, 'acf_update_value_bidirectional'], 10, 3 );
    }

    /** Add Google API key for google maps field **/
    // TODO: get key from UI connected to WP_options
    public function acf_init() {
        acf_update_setting('google_api_key', 'AIzaSyARupYJy_PTg9XHj52CKPVHri85seQ7DzM');
    }

    /**
     * Set the ACF Local JSON load point
     * @link https://www.advancedcustomfields.com/resources/local-json/
     */
    public function set_acf_json_save_point( $path ) {
        return $this->acf_json_dir;
    }

    /**
     * Set the ACF Local JSON load point
     * @link https://www.advancedcustomfields.com/resources/local-json/
     */
    public function set_acf_json_load_point( $paths ) {

        unset( $paths[ 0 ] );

        $paths[] = $this->acf_json_dir;

        return $paths;

    }

    public function acf_save_post($post_id){
        remove_action('acf/save_post', [$this, 'acf_save_post']);

        self::acf_field_term_sync(get_post($post_id));

        add_action('acf/save_post', [$this, 'acf_save_post']);
    }

    // See https://www.advancedcustomfields.com/resources/bidirectional-relationships/
    public function acf_update_value_bidirectional($value_1, $post_id_1, $field_1){
        $bidrectional_pairs = [
            ['award_publications', 'publication_awards'],
            ['award_data_projects', 'data_project_awards'],
        ];
        $field_name_to_key_mappings = [
            'award_publications' => 'field_57d1b7dedcec4',
            'publication_awards' => 'field_5ef137c4383f6',
            'award_data_projects' => 'field_5826687d82d04',
            'data_project_awards' => 'field_5ef155ba6fbae'
        ];

        $field_name_1 = $field_1['name'];

        // Break if this update filter hook call is resulting from our calls to update_field below
        if (!empty($GLOBALS['is_updating_' . $field_name_1])) return $value_1;

        foreach ($bidrectional_pairs as $bidrectional_pair){
            
            if (in_array($field_name_1, $bidrectional_pair)){
                $field_name_2 = array_values(array_diff($bidrectional_pair, [$field_name_1]))[0];
                $field_key_2 = $field_name_to_key_mappings[$field_name_2];
                
                $GLOBALS['is_updating_' . $field_name_2] = 1;

                // Add current post's ID to relationship field of corresponding posts
                if (is_array($value_1)){
                    foreach ($value_1 as $post_id_2){
                        $value_2 = get_field($field_name_2, $post_id_2, false);
                        
                        if( empty($value_2) ) {
                            $value_2 = [];
                        }
                        
                        if( in_array($post_id_1, $value_2) ) break;
                        
                        $value_2[] = $post_id_1;

                        update_field($field_key_2, $value_2, $post_id_2);

                    }
                }

                // Remove current post's ID from any corresponding posts no longer referenced
                $old_value_1 = get_field($field_name_1, $post_id_1, false);

                if( is_array($old_value_1) ) {
                    foreach( $old_value_1 as $post_id_2 ) {

                        if( is_array($value_1) && in_array($post_id_2, $value_1) ) break;

                        $value_2 = get_field($field_name_2, $post_id_2, false);

                        if( empty($value_2) ) break;

                        $pos = array_search($post_id_1, $value_2);
                        unset( $value_2[ $pos] );

                        update_field($field_key_2, $value_2, $post_id_2);
                    }

                }

                $GLOBALS['is_updating_' . $field_name_2] = 0;
                
            }
        }

        return $value_1;
    }

    //see https://searchwp.com/v3/docs/kb/intercept-advanced-custom-fields-acf-relationship-field-searches/
    public function searchwp_acf_relationship_field_search($args, $field, $post_id){
        if ( empty( $args['s'] ) || ! class_exists( 'SWP_Query' ) ) {
            return $args;
        }

        $searchwp_args = array(
            'engine' => 'default', // The SearchWP engine to use.
            's'      => $args['s'],             // Pass along the search query.
            'fields' => 'ids',                  // Return only post IDs.
        );

        if ( ! empty( $args['taxonomy' ] ) ) {
            $tax_arg = explode( ':', $args['taxonomy'] );

            $searchwp_args['tax_query'] = array(
                array(
                    'taxonomy' => $tax_arg[0],
                    'field'    => 'slug',
                    'terms'    => $tax_arg[1],
                ),
            );
        }

        if ( ! empty( $args['post_type'] ) ) {
            $searchwp_args['post_type'] = $args['post_type'];
        }

        // Tell SearchWP to NOT log this search.
        add_filter( 'searchwp\statistics\log', '__return_false' );

        // Retrieve SearchWP results.
        $results = new \SWP_Query( $searchwp_args );

        // If there are no results, we need to force ACF to reflect that.
        if ( empty( $results->posts ) ) {
            $results->posts = array( 0 );
        }

        // We're going to use SearchWP's results to handle the restrictions as outlined.
        $args['s']        = '';
        $args['order']    = '';
        $args['orderby']  = 'post__in';
        $args['post__in'] = $results->posts;

        return $args;
    }

    // Add last names to relationship query results for certain Publication, Award and Data Project entities
    public function acf_relationship_result($text, $post, $field, $post_id){
        $post_type_person_field = [
            'publication' => 'publication_authors',
            'award' => 'award_participants',
            'data_project' => 'data_project_people',
        ];
        if (isset($post_type_person_field[$post->post_type])){
            $people = get_field($post_type_person_field[$post->post_type], $post->ID);
            $last_names = array_map(function($row){
                return get_field('person_last_name', $row['person']->ID);
            }, $people);
            return $text . ' <em>(' . implode(', ', $last_names) . ')</em>';
        }
        return $text;
    }

    public function acf_post_object_result($text, $post, $field, $post_id){
        return $text . ' [ID: ' . $post->ID . ']';
    }

    static function acf_field_term_sync($post){
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
                        ['type' => $subfield_type, 'name' => $subfield_name ] = $subfield_info;
                        $save_terms = isset($subfield_info['save_terms'])  && $subfield_info['save_terms'];

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
    }
}