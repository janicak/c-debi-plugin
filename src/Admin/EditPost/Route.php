<?php

namespace C_DEBI\Admin\EditPost;

use C_DEBI\Admin\Common;
use C_DEBI\Entities;
use C_DEBI\Utilities;
use GuzzleHttp;

class Route extends Common\Route {

    public function enqueue_route_assets() {
        parent::enqueue_route_assets();
    }

    function data_to_scripts() {
        $post_type = null;

        if (isset($_REQUEST['post_type'])){
            $post_type = $_REQUEST['post_type'];
        } else if (isset($_GET['post'])){
            $post_ID = $_GET['post'];
            $post_type = get_post_type($post_ID);
        }

        return [ "post_type" => $post_type ];
    }

    static function crossref_fetch($args) {
        $DOI = $args['id'];

        // TODO: cache remote queries server-side, move to Utilities
        $crossref_res = (
            new GuzzleHttp\Client()
        )->request(
            'GET', 'http://api.crossref.org/works/' . $DOI
        );

        if ($crossref_res->getStatusCode() === 200) {
            $crossref_body = Utilities\Helpers::parseHttpResponseBody($crossref_res->getBody());
            $crossref_data = $crossref_body["message"];
            $res = (new CrossRef)->mapCrossRefToPublication($crossref_data);
        } else {
            wp_send_json_error('Error reaching CrossRef server');
        }

        return $res;
    }

    static function create_new_entities($reqs){
        $res = [];
        foreach ($reqs as $req){
            [
                "reqId" => $req_id, "sourceEntity" => $source_entity,
                "targetEntity" => $target_entity, "fields" => $fields
            ] = $req;

            switch ($target_entity){
                case "person":
                    $post_title = (CrossRef::parseCrossRefName($fields['first'], $fields['last']))["formatted_name"];
                    $res[$req_id] = Entities\People::create_person($fields, $post_title);
                    break;
                default:
                    break;
            }
        }
        return $res;
    }
}