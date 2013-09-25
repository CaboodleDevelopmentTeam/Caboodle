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

require_once(dirname(__FILE__) . '/locallib.php');

class caboodle_childlink extends caboodle_api {

    public function __construct($resourceid, $instanceid, $numresults = 20) {
        parent::__construct($resourceid, $instanceid, $numresults);
    }

    /**
     * Execute api-specific search
     *
     * @param type $query
     * @return boolean
     */
    protected function search_api($query) {
        $query = $this->clean_query_string($query);

        $url = $this->url . '/' . $query;

        if ($xmldata = $this->exec_curl($url)) {

            $xmldata = $this->parse_data($xmldata);

        } else {
            return '';
        }

        return $xmldata;
    }

    /**
     * Parse XML to array and extracts only required data
     *
     * @param type $data
     * @return array
     */
    private function parse_data($data) {

        $xml = new DOMDocument();
        $xml->loadXML($data);
        
        $count = 0;
        $ret = '';

        $title_path = "//item/title";
        $record_path = "//item/link";

        // get DOMXPath instance
        $finder = new DOMXPath($xml);
        $record_nodes = $finder->query($record_path);
        $title_nodes = $finder->query($title_path);

        // we need to be sure that all records have title and length
        if ($record_nodes->length == $title_nodes->length) {

            // foreach item
            for ($item = 0; $item < $record_nodes->length; $item++) {

                $ret[$item]['title'] = $title_nodes->item($item)->textContent;
                $ret[$item]['url'] = $record_nodes->item($item)->textContent;

            } // for

        } // if

        return $ret;
    }

} // caboodle_childlink
