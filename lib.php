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
     * @param int $resourceid
     * @param int $instanceid
     * @global resource $DB
     * @return boolean
     */
    public function is_expired($resourceid, $instanceid, $expire_after) {
        global $DB;

        $timestamp = time() - $expire_after;

        if (! $resource = $DB->get_record('caboodle_search_results', array('resourceid' => $resourceid, 'instance' => $instanceid))) {
            return false;
        }

        //mtrace(date('Y-m-d H:i:s', $resource->timestamp) . ' > ' . date('Y-m-d H:i:s', $timestamp));

        if ($resource->timestamp > $timestamp) {
            // yes, it's expired
            return true;
        } else {
            return false;
        }
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
    
    public function trim_array_elements($array) {
        
        $ret = array();
        
        foreach ($array as $key => $val) {
            $ret[$key] = trim($val);
        }
        
        return $ret;
    }
    
    public function get_urls_from_blacklist($blacklist) {
        
        $ret = array();
        
        foreach ($blacklist as $index => $data) {
            $exploded =  explode('::', $data);
            $ret[$index] = $exploded[1];
        }
        
        return $ret;
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

class caboodle_htmldump {
    
    private $courseid;
    private $label_content;
    public  $errors;
    
    public function __construct($courseid, $label_content) {
        $this->courseid = $courseid;
        $this->label_content = $label_content;
    }

    public function add_label() {
        // add label and return resource_id

    }

    private function get_module_id() {
        global $DB;

        $module = $DB->get_record('modules', array('name' => 'label'), '*', MUST_EXIST);

        return $module->id;
    }

    private function get_course_section() {
        global $DB;
        
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

        $cw = get_course_section(0, $course->id);

        return $cw->id;
    }

    private function add_to_course_sections() {
        global $DB;

        if ($DB->record_exists('course_sections', array('course' => $courseid, 'section' => 0))) {
            $sectionid = $DB->get_record('course_sections', array('course' => $courseid, 'section' => 0));

            // if sequence is not empty, add another course_module id
            if (!empty($sectionid->sequence)) {
                $sequence = $sectionid->sequence . ',' . $cmid;
            } else {
                // if sequence is empty, add course_module id
                $sequence = $cmid;
            }

            $course_section = new stdClass();
            $course_section->id = $sectionid->id;
            $course_section->course = $courseid;
            $course_section->section = 1;
            $course_section->sequence = $sequence;
            $csid = $DB->update_record('course_sections', $course_section);

        } else {
            $sequence = $cmid;

            $course_section = new stdClass();
            $course_section->course = $courseid;
            $course_section->section = 1;
            $course_section->sequence = $sequence;

            $csid = $DB->insert_record('course_sections', $course_section);

        }
    }

    private function add_course_module() {
        // add course module
        $cm = new stdClass();
        $cm->course = $courseid;
        $cm->module = $module->id; // should be retrieved from mdl_modules
        $cm->instance = $resource_id; // from mdl_resource
        $cm->section = $cw->id; // from mdl_course_sections
        $cm->visible = 1;
        $cm->visibleold = 1;
        $cm->showavailability = 1;
        $cm->added = time();

        $cmid = $DB->insert_record('course_modules', $cm);

        return $cmid;
    }

    private function clear_cache() {
        // force clear module cache
         $modulecache = new stdClass();
         $modulecache->id = $this->courseid;
         $modulecache->sectioncache = 'NULL';

         $DB->update_record('course', $modulecache);
    }
} // caboodle_htmldump