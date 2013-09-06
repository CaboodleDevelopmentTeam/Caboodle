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

class caboodle_proquest_periodicals extends caboodle_api {

    private $xusername = '';
    private $xpassword = '';

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
     * API-specific implementation
     *
     * @param type $query
     * @return type
     */
    protected function search_api($query) {

        $query = $this->clean_query_string($query);

        $url = $this->url . '?operation=searchRetrieve&version=1.2&' .
                'x-username=' . $this->xusername . '&x-password=' . $this->xpassword .
                '&maximumRecords=' . $this->_numresults . '&query=' .
                $query;

        //$xmldata = $this->exec_curl($url);
        $xmldata = file_get_contents(dirname(__FILE__) . '/sample.xml');
        $this->parse_data($xmldata);
    }

    private function parse_data($xmldata) {

        $xml = new DOMDocument();
        $xml->loadXML($xmldata);

        $zsNS = "http://www.loc.gov/zing/srw/";
        $recordNS = "http://www.loc.gov/MARC21/slim";

        $count = 0;
        $ret = '';

        foreach ($xml->getElementsByTagNameNS($recordNS, 'record') as $element) {
echo "<pre>";
var_dump($xml->getElementsByTagName('datafield')->length);
echo "</pre>";
// 'http://search.proquest/com/docview/'+tag35Code

//            $ret[$count]['title'] = $xml->getElementsByTagNameNS($dcNS, 'title')->item($count)->textContent;
//            $ret[$count]['url'] = $xml->getElementsByTagNameNS($mhubNS, 'link-to-mediahub')->item($count)->textContent;

            $count++;
        }

        return $ret;
    }

} // caboodle_proquest_periodicals