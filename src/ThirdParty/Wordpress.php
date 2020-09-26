<?php

namespace C_DEBI\ThirdParty;

class Wordpress {

    public function configure_wordpress(){
        add_filter('posts_where', [$this, 'posts_where']);
        add_filter('upload_mimes', [$this, 'upload_mimes'], 1, 1);
        add_action('parse_query', [$this, 'redirect_query'] );
    }

    public function posts_where( $where ) {
        global $wpdb;

        $where = str_replace("meta_key = 'award_participants_$", "meta_key LIKE 'award_participants_%", $where);
        $where = str_replace("meta_key = 'publication_authors_$", "meta_key LIKE 'publication_authors_%", $where);
        $where = str_replace("meta_key = 'publication_editors_$", "meta_key LIKE 'publication_editors_%", $where);
        $where = str_replace("meta_key = 'protocol_authors_$", "meta_key LIKE 'protocol_authors_%", $where);
        $where = str_replace("meta_key = 'data_project_people_$", "meta_key LIKE 'data_project_people_%", $where);
        $where = str_replace("meta_key = 'dataset_people_$", "meta_key LIKE 'dataset_people_%", $where);
        $where = str_replace("meta_key = 'award_publications_$", "meta_key LIKE 'award_publications_%", $where);
        $where = str_replace("meta_key = 'data_project_datasets_$", "meta_key LIKE 'data_project_datasets_%", $where);

        return $where;
    }

    public function upload_mimes ($mime_types) {
        $mime_types['eps'] = 'image/eps';
        $mime_types['aac'] = 'audio/x-aac';
        return $mime_types;
    }

    function redirect_query( $query ){
        if(is_archive()) {
            wp_redirect( home_url() );
            exit;
        }
    }

}