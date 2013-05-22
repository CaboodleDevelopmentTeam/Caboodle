<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Component 'caboodle', language 'en', branch 'MOODLE_24_STABLE'
 *
 * @package   caboodle
 * @author    Grzegorz Adamowicz (greg.adamowicz@enovation.ie)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 *
 *
 */
class caboodle {

    public function __construct() {
        return true;
    }

    /**
     * Get all resources
     *
     * @global resource $DB
     * @return array
     */
    public function get_resources() {
        global $DB;

        $resources = $DB->get_records('caboodle_resources', array());

        return $resources;
    } // get_resources

    /**
     * Retrieve and decode search results from db
     *
     * @global resource $DB
     * @param type $resourceid
     * @param type $instanceid
     * @return array
     */
    public function get_results($resourceid, $instanceid) {
        global $DB;

        $results = $DB->get_record('caboodle_search_results', array('resourceid' => $resourceid, 'instance' => $instanceid));

        $ret = $this->decode_search_results($results->results);

        return $ret;
    } // get_results

    /**
     * Return search string
     *
     * @global resource $DB
     * @param type $resourceid
     * @param type $instanceid
     * @return string
     */
    public function get_search_string($resourceid, $instanceid) {
        global $DB;

        $results = $DB->get_record('caboodle_search_results', array('resourceid' => $resourceid, 'instance' => $instanceid));

        return $results->searchstr;
    }

    /**
     * Return all results older than $expire_after seconds
     *
     * @global resource $DB
     * @param type $expire_after
     * @return array
     */
    public function get_all_expired_results($expire_after) {
        global $DB;

        $timestamp = time() - $expire_after;

        $sql = "SELECT id,searchstr,results,timestamp FROM {caboodle_search_results} WHERE
            `timestamp` < " . $timestamp;

        $results = $DB->get_records_sql($sql);

        return $results;
    }


    /**
     * Return all caboodle block instances
     *
     * @global resource $DB
     * @return array
     */
    public function get_all_block_instances() {
        global $DB;

        $instances = $DB->get_records('block_instances', array('blockname' => 'caboodle'), 'id,configdata');

        foreach($instances as $id => $data) {
            $instances[$id]->configdata = $this->decode_config($data->configdata);
        }

        return $instances;
    }

    /**
     * Decode configuration data
     *
     * @param type $configdata
     * @return array
     */
    private function decode_config($configdata) {

        $configdata = unserialize(base64_decode($configdata));

        return $configdata;
    }

    /**
     * See decode_config
     *
     * @param type $results
     * @return type
     */
    private function decode_search_results($results) {
        return $this->decode_config($results);
    }


} // caboodle
