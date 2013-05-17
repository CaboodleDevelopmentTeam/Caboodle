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

interface caboodle_api_interface {
    public function search($query); // execute search and return N results
}

abstract class caboodle_api implements caboodle_api_interface {
    public $name;
    public $url;

    protected $_searchid;
    protected $_searchdata;
    protected $_numresults;
    protected $_transfer_timeout = 5000; // 5000ms == 5 seconds

    /**
     * __construct
     *
     * @global resource $DB
     * @param int  $resourceid - id of resource from caboodle_resources table
     * @param int  $instanceid - id of block instance from blocks table
     * @param int  $numresults - maximum number of results to save, defaults 20
     */
    public function __construct($resourceid, $instanceid, $numresults = 20) {
        global $DB;

        $resource = $DB->get_record('caboodle_resources', array('id' => $resourceid));

        $this->name = $resource->name;
        $this->url = $resource->url;
        $this->_numresults = $numresults;

        if ($search_data = $DB->get_record('caboodle_search_results', array('resourceid' => $resourceid, 'instance' => $instanceid))) {

            $this->_searchid = $search_data->id;
            $this->_searchdata = $this->decode_results($search_data->results);

        } else {

            $this->_searchid = 0;
            $this->_searchdata = null;

        }

    } // __construct

     private function decode_results($results) {

        $results = unserialize(base64_decode($results));

        return $results;
    }

    private function encode_results($results) {

        $results = base64_encode(serialize($results));

        return $results;
    }

    protected function save_results($results) {

        return true;
    }

    protected function clean_query_string($query) {

        $query = htmlspecialchars($query,  ENT_COMPAT | ENT_HTML401, 'UTF-8');

        return $query;
    }

} // abstract
