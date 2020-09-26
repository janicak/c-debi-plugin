<?php

namespace C_DEBI\Admin\BcoDmoSync;

return [
    "source_title_field" => 'name',
    "source_id_field" => 'bco_dmo_id',
    "target_id_field" => 'dataset_bco_dmo_id',
    "target_id_field_key" => 'field_5870090899d5e',
    "plural_label" => "datasets",
    "status_percentage_increment" => 0.34,
    "field_mapping" => [
        [
            "source_field_name" => "name",
            "post_field_name" => "post_title",
            "post_field_type" => "post_title",
            "field_value_callback" => function($data){ return trim($data["name"]) ;}
        ],
        [
            "source_field_name" => "bco_dmo_id",
            "post_field_name" => "dataset_bco_dmo_id",
            "post_field_type" => "text",
            "acf_field_key" => "field_5870090899d5e",
        ],
        [
            "source_field_name" => "bco_dmo_id",
            "post_field_name" => "dataset_bco_dmo_json",
            "post_field_type" => "json",
            "acf_field_key" => "field_586ff889d7171",
            "field_value_callback" => function($data){ return json_encode( $data, JSON_UNESCAPED_SLASHES ); }
        ],
        [
            "source_field_name" => "url",
            "post_field_name" => "dataset_url",
            "post_field_type" => "text",
            "acf_field_key" => "field_5887e9ca4dc8a",
        ],
        [
            "source_field_name" => "download_url",
            "post_field_name" => "dataset_download_url",
            "post_field_type" => "text",
            "acf_field_key" => "field_5870096299d61",
        ],
        [
            "source_field_name" => "bco_dmo_state",
            "post_field_name" => "dataset_bco_dmo_state",
            "post_field_type" => "text",
            "acf_field_key" => "field_5870097399d62",
        ],
        [
            "source_field_name" => "media_type",
            "post_field_name" => "dataset_media_type",
            "post_field_type" => "text",
            "acf_field_key" => "field_5870095999d60",
        ],
        [
            "source_field_name" => "brief_desc",
            "post_field_name" => "dataset_brief_description",
            "post_field_type" => "text_area",
            "acf_field_key" => "field_5870098199d63",
        ],
        [
            "source_field_name" => "acquisition_desc",
            "post_field_name" => "dataset_acquisition_description",
            "post_field_type" => "wysiwyg",
            "acf_field_key" => "field_5870098d99d64",
        ],
        [
            "source_field_name" => "processing_desc",
            "post_field_name" => "dataset_processing_description",
            "post_field_type" => "wysiwyg",
            "acf_field_key" => "field_588277cbb72e5",
        ],
        [
            "source_field_name" => "created",
            "post_field_name" => "dataset_date_created",
            "post_field_type" => "date_picker",
            "acf_field_key" => "field_587008c399d5c",
            "field_value_callback" => function($data){ return explode( "T", $data["created"] )[ 0 ]; },
        ],
        [
            "source_field_name" => "modified",
            "post_field_name" => "dataset_date_modified",
            "post_field_type" => "date_picker",
            "acf_field_key" => "field_587008f999d5d",
            "field_value_callback" => function($data){ return explode( "T", $data["modified"] )[ 0 ]; },
        ],
        [
            "source_field_name" => "instruments",
            "post_field_name" => "dataset_instruments",
            "post_field_type" => "repeater",
            "acf_field_key" => "field_587006b599d4e",
            "subfield_mappings" => [
                [
                    "source_field_name" => "type",
                    "post_field_name" => "type",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "type",
                    "post_field_name" => "type_term",
                    "post_field_type" => "taxonomy",
                    "taxonomy" => "instrument",
                    "field_value_callback" => function($row){ return BCODMO::get_or_create_term_with_name_and_taxonomy($row["type"], "instrument"); }
                ],
                [
                    "source_field_name" => "type_desc",
                    "post_field_name" => "type_desc",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "name",
                    "post_field_name" => "name",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "desc",
                    "post_field_name" => "desc",
                    "post_field_type" => "text",
                ]
            ]
        ],
        [
            "source_field_name" => "parameters",
            "post_field_name" => "dataset_parameters",
            "post_field_type" => "repeater",
            "acf_field_key" => "field_5870082499d53",
            "subfield_mappings" => [
                [
                    "source_field_name" => "generic_name",
                    "post_field_name" => "generic_name",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "generic_name",
                    "post_field_name" => "generic",
                    "post_field_type" => "taxonomy",
                    "field_value_callback" => function($row){ return BCODMO::get_or_create_term_with_name_and_taxonomy($row['generic_name'], "parameter"); }
                ],
                [
                    "source_field_name" => "generic_desc",
                    "post_field_name" => "generic_desc",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "name",
                    "post_field_name" => "name",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "desc",
                    "post_field_name" => "desc",
                    "post_field_type" => "text",
                ]
            ]
        ],
        [
            "source_field_name" => "people",
            "post_field_name" => "dataset_people",
            "post_field_type" => "repeater",
            "acf_field_key" => "field_5870088199d58",
            "subfield_mappings" => [
                [
                    "source_field_name" => "name",
                    "post_field_name" => "name",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "affiliation",
                    "post_field_name" => "affiliation",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "name",
                    "post_field_name" => "contact",
                    "post_field_type" => "boolean",
                    "field_value_callback" => function($row, $data){
                        return isset($data["contact"]) && $data["contact"] === $row["name"];
                    }
                ],
                [
                    "source_field_name" => "name",
                    "post_field_name" => "person",
                    "post_field_type" => "post_object",
                    "field_value_callback" => function($row, $data, $row_i, $post){
                        return
                            isset($post->fields["dataset_people"]) &&
                            isset($post->fields["dataset_people"][$row_i]) &&
                            isset($post->fields["dataset_people"][$row_i]["name"]) &&
                            $post->fields["dataset_people"][$row_i]["name"] === $row["name"] &&
                            isset($post->fields["dataset_people"][$row_i]["person"])
                                ? $post->fields["dataset_people"][$row_i]["person"]
                                : null;
                    }
                ],
            ]
        ],
    ]
];