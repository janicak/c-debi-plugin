<?php

namespace C_DEBI\Admin\EditPost;
use C_DEBI\Entities;

class CrossRef {
    
    public function mapCrossRefToPublication($crossref_data) {
        $mappings = [
            [
                "field_name" => "post_title",
                "field_type" => "post_title",
                "callback" => function($d){
                    if (isset($d['title'])){
                        return [
                            'value' => $d['title'][0],
                        ];
                    }
                    return null;
                }
            ],
            [
                "field_name" => "publication_publisher_title",
                "field_type" => "text",
                "callback" => function($d){
                    if (isset($d['container-title'])){
                        return [
                            'value' => $d['container-title'][0]
                        ];
                    }
                    return null;
                }
            ],
            [
                "field_name" => "publication_url",
                "field_type" => "text",
                "callback" => function($d){
                    if (isset($d['URL'])){
                        return [
                            'value' => $d['URL'],
                        ];
                    }
                    return null;
                }
            ],
            [
                "field_name" => "publication_type",
                "field_type" => "taxonomy",
                "callback" => function($d){
                    if (isset($d['type'])){
                        return [
                            'value' => ucwords(str_replace("-", " ", $d["type"]))
                        ];
                    }
                    return null;
                }
            ],
            [
                "field_name" => "publication_authors",
                "field_type" => "repeater",
                "callback" => function($d) {
                    if (isset($d['author'])){
                        return $this->enrichCrossRefPeopleData($d["author"]);
                    }
                    return null;
                 },
            ],
            [
                "field_name" => "publication_authors",
                "field_type" => "repeater",
                "callback" => function($d) {
                    if (isset($d['editor'])){
                        return $this->enrichCrossRefPeopleData($d["editor"]);
                    }
                    return null;
                },
            ],
            [
                "field_name" => "publication_date_published",
                "field_type" => "date_picker",
                "callback" => function($d) {
                    if (isset($d['issued'])){
                        return $this->enrichCrossRefDate($d);
                    }
                    return null;
                },
            ],
        ];

        $publication = [];
        foreach ($mappings as $map) {
            [ "field_name" => $field_name, "field_type" => $field_type, "callback" => $callback ] = $map;
            $processed_data = $callback( $crossref_data );
            if( $processed_data ) {
                $publication[] = array_merge(
                    [
                        "field_name" => $field_name,
                        "field_type" => $field_type,
                        "value" => '',
                        "preview" => '',
                        "note" => ''
                    ],
                    $processed_data );
            }
        }

        return $publication;
    }

    private function enrichCrossRefDate($data){
        $derived_date_parts = $source_date_parts = $data["issued"]["date-parts"][0];

        // Crossref will at times only report the year or year + month, and we need a valid date for the field;
        // any missing parts get a "1" for January as the default month, and the 1st as the default day.
        if (count($derived_date_parts) === 1){
            array_push($derived_date_parts, 1, 1);
        } else if (count($derived_date_parts) === 2){
            array_push($derived_date_parts, 1);
        }

        // Formatting derived date as Ymd
        $Ymd = $derived_date_parts[0];
        foreach($derived_date_parts as $i => $date_part){
            $Ymd .= $i > 0 ? sprintf("%02d", $date_part) : '';
        }

        // Formatting derived date as n-j-Y
        $n_j_Y = '';
        foreach($derived_date_parts as $i => $date_part){
            $n_j_Y .= $i > 0 ? $date_part . '-' : '';
        }
        $n_j_Y .= $derived_date_parts[0];

        // Formatting provided date as n-j-Y, n-Y or Y
        $formatted_source_date = '';
        foreach($source_date_parts as $i => $part){
            $formatted_source_date .= $i > 0 ? $part . '-' : '';
        }
        $formatted_source_date .= $source_date_parts[0];

        $note = $n_j_Y !== $formatted_source_date ? "n.b.: incomplete date supplied by CrossRef: " . $formatted_source_date . "; " : "";
        $note .= "Note: please be advised that the publication date provided by CrossRef is approximate; you may wish to verify the publication date at the <a href='".$data["URL"]."' target='_blank'>publisher's website</a>.";
        
        return [
            "value" => $Ymd,
            "preview" => $n_j_Y,
            "note" => $note
        ];
    }

    private function enrichCrossRefPeopleData($data){
        $people = ["value" => [], "preview" => []];

        foreach ($data as $d){
            ["given" => $given, "family" => $family, "affiliation" => $affiliation] = $d;
            $parsed_name = $this::parseCrossRefName($given, $family);
            $people["preview"][] = $parsed_name['formatted_name'];
            $people["value"][] = [
                [
                    "field_name" => "given",
                    "field_type" => "text",
                    "value" => $given,
                    "preview" => ''
                ],
                [
                    "field_name" => "family",
                    "field_type" => "text",
                    "value" => $family,
                    "preview" => ''
                ],
                [
                    "field_name" => "affiliation",
                    "field_type" => "text",
                    "value" => count($affiliation) ? $d['affiliation'][0]["name"] : '',
                    "preview" => ''
                ],
                [
                    "field_name" => "person",
                    "field_type" => "post_object",
                    "value" => '',
                    "preview" => '',
                    "matched_entities" => array_map(
                        function($p){ return [ "text" => $p->post_title, "id" => $p->ID]; },
                        Entities\People::get_people_by_first_and_last_name($given, $family)
                    )
                ]
            ];
        }

        return $people;
    }
    
    static function parseCrossRefName($given, $family){
        
        $given_no_periods = trim(str_replace('.', '', $given));
        $given_parts = explode(' ', $given_no_periods);

        // Given name has no spaces after trimming and removing periods
        if (count($given_parts) == 1) {
            $given_part = $given_parts[0];
            // Case: AB
            if (preg_match('/^\D{2}$/', $given_part)) {
                $first = substr($given_part, 0, 1);
                $second = substr($given_part, 1, 2);
                $formatted_name = $first . '. ' . $second . '. ' . $family;
                $name = array(
                    'formatted_name' => $formatted_name,
                    'first' => $first,
                    'middle' => $second,
                    'last' => $family,
                );
            // Case: A-B
            } else if (preg_match('/^\D-\D$/', $given_part)) {
                $firstParts = explode('-', $given_part);
                $formatted_name = $firstParts[0] . '.-' . $firstParts[1] . '. ' . $family;
                $name = array(
                    'formatted_name' => $formatted_name,
                    'first' => $given_part,
                    'last' => $family,
                );
            // Case: Alpha-beta
            } else if (preg_match('/^\D.+-\.+$/', $given_part)) {
                $formatted_name = $given_part . ' ' . $family;
                $name = array(
                    'formatted_name' => $formatted_name,
                    'first' => $given_part,
                    'last' => $family,
                );
            // Case: A
            } else if (preg_match('/^\D$/', $given_part)) {
                $formatted_name = $given_part . '. ' . $family;
                $name = array(
                    'formatted_name' => $formatted_name,
                    'first' => $given_part,
                    'last' => $family,
                );
            // Case: Alpha
            } else {
                $formatted_name = $given_part . ' ' . $family;
                $name = array(
                    'formatted_name' => $formatted_name,
                    'first' => $given_part,
                    'last' => $family,
                );
            }

        // Given name has spaces after trimming and removing periods
        } else {
            // Case: The first given name part is a single letter
            if (strlen($given_parts[0]) == 1) {
                $part1 = $given_parts[0] . '. ';
                //Case: A B
                if (strlen($given_parts[1]) == 1) {
                    $part2 = $given_parts[1] . '. ';
                //Case: A Beta
                } else {
                    $part2 = $given_parts[1] . ' ';
                }
                $formatted_name = $part1 . $part2 . $family;
                $name = array(
                    'formatted_name' => $formatted_name,
                    'first' => $given_parts[0],
                    'middle' => $given_parts[1],
                    'last' => $family,
                );
            // Case: The first given name part contains more than a single letter
            } else {
                // A- B
                if (preg_match('/^\D-$/', $given_parts)) {
                    $part1 = str_replace('-','',$given_parts[0]) . '.-';
                    if (strlen($given_parts[1]) == 1) {
                        $part2 = $given_parts[1] . '. ';
                    } else {
                        $part2 = $given_parts[1] . ' ';
                    }
                    $formatted_name = $part1 . $part2 . $family;
                    $name = array(
                        'formatted_name' => $formatted_name,
                        'first' => $given_parts[0] . $given_parts[1],
                        'last' => $family,

                    );
                // Alpha ...
                } else {
                    $part1 = $given_parts[0] . ' ';
                    //Alpha B
                    if (strlen($given_parts[1]) == 1) {
                        $part2 = $given_parts[1] . '. ';
                    //Alpha Beta
                    } else {
                        $part2 = $given_parts[1] . ' ';
                    }
                    $formatted_name = $part1 . $part2 . $family;
                    $name = array(
                        'formatted_name' => $formatted_name,
                        'first' => $given_parts[0],
                        'middle' => $given_parts[1],
                        'last' => $family,
                    );
                }
            }
        }
        return $name;
    }

}