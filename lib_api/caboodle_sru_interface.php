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

class caboodle_sru_interface extends caboodle_api {

    /**
     * __construct
     *
     * @param type $resourceid
     * @param type $instanceid
     * @param type $numresults
     */
    public function __construct($resourceid, $instanceid, $numresults = 20, $resourceresourceid, $tresultsresourceid) {
        parent::__construct($resourceid, $instanceid, $numresults, $resourceresourceid, $tresultsresourceid);
    }

    /**
     * Execute api-specific search
     *
     * @param type $query
     * @return boolean
     */
    protected function search_api($query) {

        $query = $this->clean_query_string($query);

        $url = $this->url . '?version=1.1&operation=searchRetrieve&query=' .
                $query . '&maximumRecords=' . $this->_numresults;


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

        $mhubNS = "http://m2m.edina.ac.uk/ns/mediahub";
        $dcNS = "http://purl.org/dc/elements/1.1/";

        $count = 0;
        $ret = '';

        foreach ($xml->getElementsByTagNameNS($mhubNS, 'record') as $element) {

            $ret[$count]['title'] = $xml->getElementsByTagNameNS($dcNS, 'title')->item($count)->textContent;
            $ret[$count]['url'] = $xml->getElementsByTagNameNS($mhubNS, 'link-to-mediahub')->item($count)->textContent;

            $count++;
        }

        return $ret;
    }

} // caboodle_sru_interface
