<?php

namespace C_DEBI\Admin\BcoDmoSync;

return [
    "source_title_field" => 'project_title',
    "source_id_field" => 'bco_dmo_id',
    "target_id_field" => 'data_project_bco_dmo_id',
    "target_id_field_key" => 'field_587fc7e85cc5b',
    "plural_label" => "data projects",
    "status_percentage_increment" =>  0.197,
    "field_mapping" => [
        [
            "source_field_name" => "project_title",
            "post_field_name" => "post_title",
            "post_field_type" => "post_title",
            "field_value_callback" => function($data){ return trim($data["project_title"]) ;}
        ],
        [
            "source_field_name" => "bco_dmo_id",
            "post_field_name" => "data_project_bco_dmo_id",
            "post_field_type" => "text",
            "acf_field_key" => "field_587fc7e85cc5b"
        ],
        [
            "source_field_name" => "bco_dmo_id",
            "post_field_name" => "data_project_bco_dmo_json",
            "post_field_type" => "json",
            "acf_field_key" => "field_587fc80c5cc5c",
            "field_value_callback" => function($data){ return json_encode( $data, JSON_UNESCAPED_SLASHES  ) ;}
        ],
        [
            "source_field_name" => "acronym",
            "post_field_name" => "data_project_acronym",
            "post_field_type" => "text",
            "acf_field_key" => "field_587fd84bdae00",
        ],
        [
            "source_field_name" => "url",
            "post_field_name" => "data_project_url",
            "post_field_type" => "text",
            "acf_field_key" => "field_582616cbc9297",
        ],
        [
            "source_field_name" => "desc",
            "post_field_name" => "data_project_description",
            "post_field_type" => "wysiwyg",
            "acf_field_key" => "field_587fd95bd4754",
        ],
        [
            "source_field_name" => "created",
            "post_field_name" => "data_project_date_created",
            "post_field_type" => "date_picker",
            "acf_field_key" => "field_587fd80fdadff",
            "field_value_callback" => function($data){ return explode( "T", $data["created"] )[0]; }
        ],
        [
            "source_field_name" => "modified",
            "post_field_name" => "data_project_date_modified",
            "post_field_type" => "date_picker",
            "acf_field_key" => "field_586d67364b0d9",
            "field_value_callback" => function($data){ return explode( "T", $data["modified"] )[0]; }
        ],
        [
            "source_field_name" => "datasets",
            "post_field_name" => "data_project_datasets",
            "post_field_type" => "repeater",
            "acf_field_key" => "field_5826162fc9293",
            "subfield_mappings" => [
                [
                    "source_field_name" => "name",
                    "post_field_name" => "name",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "bco_dmo_id",
                    "post_field_name" => "bco_dmo_dataset_id",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "bco_dmo_id",
                    "post_field_name" => "dataset",
                    "post_field_type" => "post_object",
                    "field_value_callback" => function($row){
                        return (BCODMO::get_or_create_post_with_meta_value('dataset', 'dataset_bco_dmo_id', $row['bco_dmo_id']))->ID;
                    }
                ],
            ]
        ],
        [
            "source_field_name" => "people",
            "post_field_name" => "data_project_people",
            "post_field_type" => "repeater",
            "acf_field_key" => "field_587fdc9348352",
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
                    "source_field_name" => "role",
                    "post_field_name" => "role",
                    "post_field_type" => "text",
                ],
                [
                    "source_field_name" => "name",
                    "post_field_name" => "person",
                    "post_field_type" => "post_object",
                    "field_value_callback" => function($row, $data, $row_i, $post){
                        return
                            isset($post->fields["data_project_people"]) &&
                            isset($post->fields["data_project_people"][$row_i]) &&
                            isset($post->fields["data_project_people"][$row_i]["name"]) &&
                            $post->fields["data_project_people"][$row_i]["name"] === $row["name"] &&
                            isset($post->fields["data_project_people"][$row_i]["person"])
                                ? $post->fields["data_project_people"][$row_i]["person"]
                                : null;
                    }
                ],
            ]
        ]
    ]
];