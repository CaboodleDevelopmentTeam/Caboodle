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

        $url = $this->url . '?version=1.1&operation=searchRetrieve&query=' .
                $query . '&maximumRecords=' . $this->_numresults;

        if (!function_exists('curl_init')) {
            $this->lasterror = 'cURL NOT installed!';
            return '';
        }
        
        $curl = curl_init($url);

        // set curl options
        $options = array(
            CURLOPT_CONNECTTIMEOUT_MS => $this->_transfer_timeout,  // set default connection timeout
            CURLOPT_TIMEOUT_MS => $this->_transfer_timeout,         // set default transfer timeout
            CURLOPT_RETURNTRANSFER => true,                         // return all data from connection
            CURLOPT_FAILONERROR => true,                            // pay attention to http errors
            CURLOPT_VERBOSE    => true                              // show verbose output to stderr
        );

        curl_setopt_array($curl, $options);

        if($xmldata = curl_exec($curl)) {

            $xmldata = $this->parse_data($xmldata);

        } else {
            $this->lasterror = curl_error($curl);
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

//            mtrace('Title: ' . $xml->getElementsByTagNameNS($dcNS, 'title')->item($count)->textContent);
//            mtrace('URL: ' . $xml->getElementsByTagNameNS($mhubNS, 'link-to-mediahub')->item($count)->textContent);

            $ret[$count]['title'] = $xml->getElementsByTagNameNS($dcNS, 'title')->item($count)->textContent;
            $ret[$count]['url'] = $xml->getElementsByTagNameNS($mhubNS, 'link-to-mediahub')->item($count)->textContent;

            $count++;
        }

        return $ret;
    }

    /**
     * Strip html and php tags, convert all applicable characters to html entities
     * url encodes string (eg. space becomes %20)
     *
     * @param type $query
     * @return type
     */
    protected function clean_query_string($query) {

        $query = rawurlencode(htmlentities(strip_tags($query)));

        return $query;
    }

} // caboodle_sru_interface
