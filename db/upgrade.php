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

    return true;
}