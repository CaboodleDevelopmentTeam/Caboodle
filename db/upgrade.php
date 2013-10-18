<?php

/**
 *
 *
 * @package   caboodle
 * @author    Grzegorz Adamowicz (greg.adamowicz@enovation.ie)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function xmldb_block_caboodle_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013090500) {

        $new_resourcetype = new stdClass();
        $new_resourcetype->typename = 'ProQuest periodicals (SRU)';
        $new_resourcetype->typeclass = 'caboodle_proquest_periodicals';

        $newid = $DB->insert_record('caboodle_resource_types', $new_resourcetype);

        $resource = new stdClass();
        $resource->type = $newid;
        $resource->name = 'British Periodicals (ProQuest)';
        $resource->url = 'http://fedsearch.proquest.com/search/sru/britishperiodicals';

        $newresourceid = $DB->insert_record('caboodle_resources', $resource);

        upgrade_block_savepoint(true, 2013090500, 'caboodle');
    }

    if ($oldversion < 2013091200) {

        $new_resourcetype = new stdClass();
        $new_resourcetype->typename = 'Childlink';
        $new_resourcetype->typeclass = 'caboodle_childlink';

        $newid = $DB->insert_record('caboodle_resource_types', $new_resourcetype);

        $resource = new stdClass();
        $resource->type = $newid;
        $resource->name = 'Childlink';
        $resource->url = 'http://members.childlink.co.uk/opensearch/node';

        $newresourceid = $DB->insert_record('caboodle_resources', $resource);

        upgrade_block_savepoint(true, 2013091200, 'caboodle');
    }
    

    if ($oldversion < 2013091701) {

        $table = new xmldb_table('caboodle_resources');
        $field = new xmldb_field('repository_url');

        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '', 'name');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // add URLs to all predefined repositories
        $records = $DB->get_records('caboodle_resources');
        
        foreach ($records as $rid => $record) {
            //        $resource->repository_url = 'http://jiscmediahub.ac.uk/';
            //        $resource->repository_url = 'http://www.proquest.co.uk/';
            //        $resource->repository_url = 'http://www.childlink.co.uk/';

            // determine order by order of adding repositories to db
            switch ($record->id) {
                case 1:
                    $record->repository_url = 'http://jiscmediahub.ac.uk/';
                    break;
                case 2:
                    $record->repository_url = 'http://www.proquest.co.uk/';
                    break;
                case 3:
                    $record->repository_url = 'http://www.childlink.co.uk/';
                    break;
                default:
                    $record->repository_url = '';
            }
            
            $DB->update_record('caboodle_resources', $record);
        }

        upgrade_block_savepoint(true, 2013091701, 'caboodle');
    }
    
    if ($oldversion < 2013101800) {

        $new_resourcetype = new stdClass();
        $new_resourcetype->typename = 'ebsco';
        $new_resourcetype->typeclass = 'caboodle_ebsco';

        $newid = $DB->insert_record('caboodle_resource_types', $new_resourcetype);

        $resource = new stdClass();
        $resource->type = $newid;
        $resource->name = 'Ebsco';
        $resource->url = 'http://eit.ebscohost.com/Services/SearchService.asmx/Search?';
        $resource->repository_url = 'http://search.ebscohost.com/';
        
        //ticket #19479
        $sql = "SELECT name FROM mdl_caboodle_resources
            WHERE name='Ebsco'";
        $result = $DB->get_record_sql($sql);
        if($result){
            $DB->delete_records('caboodle_resources', array('name' => $resource->name));
        }

        $newresourceid = $DB->insert_record('caboodle_resources', $resource);

        upgrade_block_savepoint(true, 2013101800, 'caboodle');
    }

    return true;
}