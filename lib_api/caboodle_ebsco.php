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

/**
 * Class caboodle_default returns static query results for testing purposes
 * It may be extended to use real API in the future
 */
class caboodle_ebsco extends caboodle_api {

    private $xusername = '';
    private $xpassword = '';
    
    private $pwd = '';
    private $authType = '';
    private $ipprof = '';
    private $prof = '';
    
    
    public function __construct($resourceid, $instanceid, $numresults = 20) {
        parent::__construct($resourceid, $instanceid, $numresults);
    }

    protected function search_api($query) {

        $query = $this->clean_query_string($query);

        $url = $this->url . '?operation=searchRetrieve&version=1.2&' .
                'x-username=' . $this->xusername . '&x-password=' . $this->xpassword .
                '&maximumRecords=' . $this->_numresults . '&query=title=' .
                $query;

        if ($xmldata = $this->exec_curl($url)) {

            $xmldata = $this->parse_data($xmldata);

        } else {
            return '';
        }

        return $xmldata;
    }

    private function parse_data($xmldata) {
        // we'll add document ID at the end of this URL
        $url_prefix = 'http://support.ebscohost.com/';
        // empty output by default
        $ret = '';

        $xml = new DOMDocument();
        $xml->loadXML($xmldata);

        $recordNS = "http://epnet.com/webservices/SearchService/Response/2007/07/";

        // set filters including namespace we register below
        $title_path = "//SearchResults:SearchResults";
        ///header/controlInfo/artinfo/tig/atl
        $record_path = "//records:records/record:datafield[@tag='035']/record:subfield[@code='a']";

        // get DOMXPath instance
        $finder = new DOMXPath($xml);
        
        $finder->registerNameSpace('SearchResults', $recordNS);

        // find all nodes
        $record_nodes = $finder->query($record_path);
        $title_nodes = $finder->query($title_path);
        echo "<pre>:" .var_dump($title_nodes). "</pre>"; 
        

        // we need to be sure that all records have title and length
        if ($record_nodes->length == $title_nodes->length) {

            // foreach item
            for ($item = 0; $item < $record_nodes->length; $item++) {
//                echo "<pre>";
//                var_dump($record_nodes->item($item)->textContent);
//                var_dump($title_nodes->item($item)->textContent);
//                echo "</pre>";

                $ret[$item]['title'] = $title_nodes->item($item)->textContent;
                $ret[$item]['url'] = $url_prefix . $record_nodes->item($item)->textContent;
            }

        }

        return $ret;
    }

}
