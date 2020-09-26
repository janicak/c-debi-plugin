<?php

namespace C_DEBI\Admin\BcoDmoSync;

use C_DEBI\Admin\Common as Common;

class Route extends Common\Route
{
    static function trigger_bcodmo_sync($args){

        foreach($args as $k => &$v ){ $v = $v === 'true'; } // Convert true/false strings to booleans

        return (new BCODMO)->init_update(
            $args['trigger_sync'],
            $args['find_unlinked_people'],
            $args['use_cache']
        );
    }

    static function trigger_link_people($people){
        return (new BCODMO)->link_names_to_people($people);
    }

    static function check_bcodmo_sync_status(){
        return BCODMO::get_process_status();
    }

    static function find_unlinked_people() {
        return BCODMO::find_unlinked_people();
    }

}
