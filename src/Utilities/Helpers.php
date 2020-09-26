<?php

namespace C_DEBI\Utilities;

class Helpers {

    static function parseHttpResponseBody($body) {
        $body_std_obj = json_decode((string) $body);
        $body_assc_array = json_decode(json_encode($body_std_obj), true);
        return $body_assc_array;
    }

}