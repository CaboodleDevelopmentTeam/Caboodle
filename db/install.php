<?php

function xmldb_block_caboodle_install() {
    global $DB;

    // repository types:
    $caboodle_resource_types = array (
                                'Default'       => 'caboodle_default',      // class name
                                'SRU interface' => 'caboodle_sru_interface' // class name
                            );

    // default repositories:
    $caboodle_resources = array(
                            0 => array (
                                'name' => "Jisc MediaHub (SRU interface)",
                                'type' => 'caboodle_sru_interface',
                                'url'  => "http://m2m.edina.ac.uk/sru/mediahub"
                            )
                        );

    foreach ($caboodle_resource_types as $repo_name => $repo_class) {
        $record = new stdClass();
        $record->typename = $repo_name;
        $record->typeclass = $repo_class;

        $DB->insert_record('caboodle_resource_types', $record);
        unset($record);
    }

    foreach ($caboodle_resources as $repo_id => $repo_data) {

        $record = new stdClass();
        $record->name = $repo_data['name'];
        $record->url = $repo_data['url'];

        // get type
        if ($type = $DB->get_record('caboodle_resource_types', array('typeclass' => $repo_data['type']))) {
            $record->type = $type->id;
        } else {
            $type = $DB->get_record('caboodle_resource_types', array('typeclass' => 'caboodle_default'), '*', MUST_EXIST);

            $record->type = $type->id;
        }

        $DB->insert_record('caboodle_resources', $record);
        unset($record);
    }


}
