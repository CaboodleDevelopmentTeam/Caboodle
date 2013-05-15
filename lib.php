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
 * Strings for component 'caboodle', language 'en', branch 'MOODLE_24_STABLE'
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
    public static function get_resources() {
        global $DB;

        $resources = $DB->get_records('caboodle_resources', array());

        return $resources;
    } // get_resources

    public static function get_results($resourceid, $instanceid) {
        global $DB;

//        $valid_timestamp = 0;
//
//        // get all results matching $resid
//        $sql = "SELECT searchstr, results FROM {caboodle_search_results} WHERE " .
//               "$resid = '" . $resid . "' " .
//               "AND instance = '" . $instanceid . "' " .
//               "AND timestamp > " . $valid_timestamp;

        $results = $DB->get_records('caboodle_search_results', array('resourceid' => $resid, 'instance' => $instanceid));

        return $results;
    } // get_results


} // class
