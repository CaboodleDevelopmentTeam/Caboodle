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
    
    private $pwd = '';
    private $db = '';
    private $prof = '';
    
    
    public function __construct($resourceid, $instanceid, $numresults = 20, $resourceresourceid, $tresultsresourceid) {
        parent::__construct($resourceid, $instanceid, $numresults, $resourceresourceid, $tresultsresourceid);
    }

    protected function search_api($query) {

        $query = $this->clean_query_string($query);

        $url = $this->url . 'prof=' . $this->prof . '&pwd=' . $this->pwd . '&query=' .
                $query . '&db=' . $this->db . '&numrec=' . $this->_numresults ;

        //var_dump($url);
        if ($xmldata = $this->exec_curl($url)) {

            $xmldata = $this->parse_data($xmldata);

        } else {
            return '';
        }

        return $xmldata;
    }

    private function parse_data($xmldata) {
        
        $url_prefix = 'http://eit.ebscohost.com/Services/SearchService.asmx/Search?';
        // empty output by default
        $ret = '';

        $xml = new DOMDocument();
        $xml->loadXML($xmldata);

        $recordNS = "http://epnet.com/webservices/SearchService/Response/2007/07/";

        // set filters including namespace we register below
        $title_path = "//rec/header/controlInfo/artinfo/tig/atl";
        $record_path = "//rec/plink";

        // get DOMXPath instance
        $finder = new DOMXPath($xml);
        
        $finder->registerNameSpace('rec', $recordNS);

        // find all nodes
        $record_nodes = $finder->query($record_path);
        $title_nodes = $finder->query($title_path);
        //echo "<pre>:" .var_dump($title_nodes). "</pre>"; 
        

        // we need to be sure that all records have title and length
        if ($record_nodes->length == $title_nodes->length) {
            // foreach item
            for ($item = 0; $item < $record_nodes->length; $item++) {
                $ret[$item]['title'] = $title_nodes->item($item)->textContent;
                $ret[$item]['url'] = $record_nodes->item($item)->textContent;
            }

        }

        return $ret;
    }

}
