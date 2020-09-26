<?php

namespace C_DEBI\ThirdParty;

class SearchWP {

    /**
     * Configure the search index plugin
     *
     * @since    1.0.0
     */
    public function configure_search_index() {
        if (is_plugin_active('searchwp/index.php')) {
            $this->configure_searchwp();
        }
    }

    private function configure_searchwp() {

        add_filter('searchwp\install\engine\settings', [$this, 'searchwp_initial_engine_settings']);

        add_filter( 'searchwp\source\post\attributes\meta', [$this, 'searchwp_custom_fields'], 20, 2 );

        // For DEV purposes, limit index posts to a subset
        //add_filter( 'searchwp\post__not_in', [$this, 'searchwp_prevent_indexing'], 20, 2 );

        // For DEV purposes, use alternate indexer to avoid local environment SSL issues
        //add_filter( 'searchwp\indexer\alternate', '__return_true' );

        add_filter( 'searchwp\source\post\global_excerpt', '__return_true' );

    }

    public function searchwp_prevent_indexing($ids){
        $post_types = [ 'award', 'data_project', 'dataset', 'page', 'post', 'protocol', 'publication', 'newsletter'];

        $post_type_sample_size = 1;

        $all_ids = get_posts(['post_type' => $post_types, 'fields' => 'ids', 'posts_per_page' => -1]);

        //Limit queries for testing
        $post_types = ['award'];

        $include_ids = array_reduce($post_types, function($acc, $post_type) use ($post_type_sample_size) {
            $post_ids = get_posts([
                'post_type' => $post_type,
                'fields' => 'ids',
                'posts_per_page' => $post_type_sample_size
            ]);
            return array_merge($acc, $post_ids);
        }, []);

        $exclude_ids = array_diff($all_ids, $include_ids);

        return array_merge($ids, $exclude_ids);
    }

    public function searchwp_custom_fields($meta_value, $args){
        ["meta_key" => $meta_key, "post_id" => $post_id ] = $args;
        $return_value = $meta_value;

        $post_acf_field_groups = acf_get_field_groups( ['post_type' => get_post_type($post_id)] );

        $post_acf_fields_info = array_reduce($post_acf_field_groups, function($acc, $group_key){

            $acf_group_fields = acf_get_fields($group_key);
            return array_merge($acc, $acf_group_fields);

        },[]);

        $post_acf_field_info = array_reduce($post_acf_fields_info, function($acc, $field) use ($meta_key) {

            return $field['name'] === $meta_key ? $field : $acc;

        }, null);

        if ($post_acf_field_info){
            ['name' => $field_name ] = $post_acf_field_info;
            $field_value = get_field( $field_name, $post_id );

            $field_text_parts = $this->get_acf_field_text_parts($post_acf_field_info, $field_value);

            $return_value = implode( " ", $field_text_parts );
        }

        return $return_value;
    }

    private function get_acf_field_text_parts($post_acf_field_info, $field_value){
        $field_text_parts = [];

        if ($field_value){
            [ 'type' => $field_type ] = $post_acf_field_info;

            switch( $field_type ) {

                case 'repeater':
                    foreach( $field_value as $repeater_row ) {

                        foreach($post_acf_field_info['sub_fields'] as $subfield_info){
                            ['name' => $subfield_name ] = $subfield_info;
                            $subfield_value = $repeater_row[ $subfield_name ];

                            $subfield_text_parts = $this->get_acf_field_text_parts($subfield_info, $subfield_value);

                            $field_text_parts = array_merge($field_text_parts, $subfield_text_parts);
                        }
                    }
                    break;

                case 'relationship': case 'post_object':
                    if( is_array( $field_value ) ) {
                        foreach( $field_value as $rel_post ) {
                            $field_text_parts[] = ( get_post( $rel_post ) )->post_title;
                        }
                    } else {
                        $field_text_parts[] = ( get_post( $field_value ) )->post_title;
                    }
                    break;

                case 'taxonomy':
                    ['taxonomy' => $taxonomy ] = $post_acf_field_info;
                    if( is_array( $field_value ) ) {
                        foreach( $field_value as $term ) {
                            $field_text_parts[] = ( get_term( $term, $taxonomy ) )->name;
                        }
                    } else {
                        $field_text_parts[] = ( get_term( $field_value, $taxonomy ) )->name;
                    }
                    break;

                case 'wysiwyg':
                    $field_text_parts[] = wp_strip_all_tags( $field_value );
                    break;

                default:
                    $field_text_parts[] = $field_value;
                    break;

            }
        }

        return array_values(array_unique($field_text_parts));
    }

    // TODO: do anything with this?
    public function searchwp_initial_engine_settings( $settings ) {

        return $settings;

    }

}
