<?php

namespace C_DEBI\Entities;

class Datasets {

    static function acf_save_post($post_id){
        self::update_post_terms($post_id);
    }

    // TODO: see if you still need this
    static function update_post_terms($post_id){

        $instruments = get_field( 'dataset_instruments', $post_id );
        if ($instruments){
            $instrument_term_ids = array_reduce($instruments, function($acc, $row){
                if ($row["type_term"]){
                    $acc[] = intval($row["type_term"]);
                }
                return array_unique($acc);
            }, []);
            wp_set_object_terms( intval($post_id), $instrument_term_ids, 'instrument', true);
        }


        $parameters = get_field( 'dataset_parameters', $post_id );
        if ($parameters){
            $parameter_term_ids = array_reduce($parameters, function($acc, $row){
                if ($row["generic"]){
                    $acc[] = intval($row["generic"]);
                }
                return array_unique($acc);
            }, []);
            wp_set_object_terms( intval($post_id), $parameter_term_ids, 'parameter', true);
        }

    }
}