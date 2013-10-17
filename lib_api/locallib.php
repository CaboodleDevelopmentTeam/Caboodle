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
    public function search($query);         // execute search and return N results

}

abstract class caboodle_api implements caboodle_api_interface {
    public $name;                           // api name
    public $url;                            // api main url (without search string)
    public $lasterror;                      // last error from search api

    protected $_searchid;
    protected $_searchstr;
    protected $_searchdata;
    protected $_numresults;
    protected $_transfer_timeout = 10000;    // 5000ms == 5 seconds

    private $resourceid;
    private $instanceid;

    /**
     * __construct
     *
     * @global resource $DB
     * @param int  $resourceid - id of resource from caboodle_resources table
     * @param int  $instanceid - id of block instance from blocks table
     * @param int  $numresults - maximum number of results to save, defaults 20
     */
    public function __construct($resourceid, $instanceid, $numresults = 20, $resourceresourceid, $tresultsresourceid) {
        global $DB;
        $this->resourceid = $resourceid;
        $this->instanceid = $instanceid;

        //$resource = $DB->get_record('caboodle_resources', array('id' => $resourceid));
        $resource = $resourceresourceid;

        $this->name = $resource->name;
        $this->url = $resource->url;
        $this->_numresults = $numresults;

        //if ($search_data = $DB->get_record('caboodle_search_results', array('resourceid' => $resourceid, 'instance' => $instanceid))) {
        if ($search_data = $tresultsresourceid) {
            $this->_searchid = $search_data->id;
            $this->_searchstr = $search_data->searchstr;
            $this->_searchdata = $this->decode_results($search_data->results);

        } else {

            $this->_searchid = 0;
            $this->_searchdata = null;

        }

    } // __construct

    /**
     * Execute search
     *
     * @param string $query
     * @return array
     */
    public function search($query) {
        // check if anything changed
        if (strcmp($this->_searchstr, $query) != 0) {
            $this->_searchstr = $query;
        }

        $results = $this->search_api($query);

        // update search data
        $this->_searchdata = $results;

        return $results;
    }

    /**
     * This has to be implemented in api class
     * search_api method is executing api-specific search
     *
     * @param type $query
     * @throws moodle_exception
     */
    protected abstract function search_api($query);

    /**
     * Exec cURL
     *
     * @param type $url
     * @return string|boolean
     */
    protected function exec_curl($url) {
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
            return $xmldata;
        } else {
            $this->lasterror = curl_error($curl);
            return false;
        }
    } // exec curl

    /**
     * Unserializes and base64_decode search results saved in db
     *
     * @param string $results
     * @return array
     */
    private function decode_results($results) {

        $results = unserialize(base64_decode($results));

        return $results;
    }

    /**
     * Encodes search results before saving to db
     *
     * @param array $results
     * @return string
     */
    private function encode_results($results) {

        $results = base64_encode(serialize($results));

        return $results;
    }

    /**
     * Saving search results do db (insering a new record od updating existing)
     *
     * @global resource $DB
     * @return boolean
     */
    public function save_results() {
        global $DB;

        $record = new stdClass();
        $record->resourceid = $this->resourceid;
        $record->instance = $this->instanceid;
        $record->searchstr = $this->_searchstr;
        $record->results = $this->encode_results($this->_searchdata);
        $record->timestamp = time();

        if ($this->_searchid > 0) {
            $record->id = $this->_searchid;

            $DB->update_record('caboodle_search_results', $record);
        } else {
            $this->_searchid = $DB->insert_record('caboodle_search_results', $record);
        }

        return true;
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

    /**
     * implements_configuration informs if API has any configuration options (@TODO)
     * return true if yes and override get_configuration
     * return false if no
     *
     * @return boolean
     */
    public static function implements_configuration() {
        return false;
    }
    
    /**
     * Configuration implementation, all options as in settings.php (@TODO)
     * 
     * @return boolean
     */
    public static function get_configuration() {
        return false;
    }

} // abstract
