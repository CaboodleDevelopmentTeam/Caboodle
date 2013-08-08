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

// get local lib
require_once($CFG->dirroot . '/blocks/caboodle/lib.php');


class block_caboodle extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_caboodle');
    }

    function get_content() {
        global $CFG, $OUTPUT, $DB;

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        $this->content->text = '';
        if (empty($currentcontext)) {
            return $this->content;
        }

        $this->content->text .= '<div class="caboodle_block_container">';

        // show search form if user search is enabled
        if ($this->config->student_search) {
            $this->content->text .= $this->get_search_form();
        }

        // show user search results (if any)
        if (!empty($_SESSION['caboodle_usersearch_str'][$this->instance->id]) || (!is_null(optional_param('caboodlesearch', NULL, PARAM_ALPHANUM))) ) {
            $this->content->text .= $this->get_user_search();
        }

        if (array_key_exists('search_term', $_SESSION) && array_key_exists('search_id', $_SESSION) && $_SESSION['search_id'] == $this->instance->id) {
            $search_str = $_SESSION['search_term'];
            unset($_SESSION['search_term']);
            unset($_SESSION['search_id']);
        } else {
//            if ($search_record = $DB->get_record('caboodle_search_results', array('instance' => $this->instance->id))) {
            $search_str = $this->config->search;
            //$search_str = $search_record->searchstr;
        }
        
        if (!empty($search_str)) {
            // get all resources
            $caboodle = new caboodle();
            $resources = $caboodle->get_resources();

            $this->content->text .= get_string('search_on', 'block_caboodle', $search_str);
            foreach ($resources as $resourceid => $resource) {
                if ($this->config->resource[$resourceid] == 1) {
                    $this->content->text .= "<h4>" . $resource->name . "</h4>";

                    $results = $caboodle->get_results($resourceid, $this->instance->id);

                    // if results are empty, no search has been performed yet, we can do that now
                    if (empty($results)) {
                        $results = $this->perform_search($resourceid, true, $search_str);
                    }

                    $this->content->text .= '<ul class="caboodle_results">';

                    if (!empty($results)) {
                        // get search string saved in DB
                        $old_search_str = $caboodle->get_search_string($resourceid, $this->instance->id);

                        // check if searc string in DB has ben updated with the one in configuration
                        if (strcmp($search_str, $old_search_str) == 0) {

                            // get and filter blacklist urls
                            $blacklist = $caboodle->trim_array_elements($this->get_blacklist());
                            $blacklist = $caboodle->get_urls_from_blacklist($blacklist);
                            $count = 0;

                            // display list of elements
                            foreach ($results as $rid => $rdata) {

                                if (!in_array($rdata['url'], $blacklist) && $count < $this->config->search_items_displayed) {
                                    $this->content->text .= '<li class="caboodle_results_item" style="margin: 3px 0;">';
                                    $this->content->text .= '<a href="' . $rdata['url']  .'" target="_blank">' . $rdata['title'] . '</a>';
                                    $this->content->text .= "</li>";
                                    $count++;
                                } // if

                            } // foreach

                        } else {
                            //$this->content->text .= '<li class="caboodle_results_item" style="margin: 3px 0;">' . get_string('search_not_performed', 'block_caboodle') . '</li>';
                            // search string has changed, execute search and save all data
                            $results = $this->perform_search($resourceid, true, $search_str, true);

                            $this->content->text .= '<ul class="caboodle_results">';

                            // new search criteria, clear black list
                            $blacklist = $caboodle->trim_array_elements($this->get_blacklist());
                            $count = 0;

                            // display list of elements
                            foreach ($results as $rid => $rdata) {

                                if (!in_array($rdata['url'], $blacklist) && $count < $this->config->search_items_displayed) {
                                    $this->content->text .= '<li class="caboodle_results_item" style="margin: 3px 0;">';
                                    $this->content->text .= '<a href="' . $rdata['url']  .'" target="_blank">' . $rdata['title'] . '</a>';
                                    $this->content->text .= "</li>";
                                    $count++;
                                } // if

                            } // foreach

                        }

                    } else {
                        // no results
                        $this->content->text .=  '<li class="caboodle_results_item">'. get_string('nothing_found', 'block_caboodle') . '</li>';
                    }

                    $this->content->text .= "</ul>";

                } // if resource is enabled
            } // foreach resources

        } else {
            // no search string
            $this->content->text .= '<h3>'. get_string('nosearchstring', 'block_caboodle') . '</h3>';
        }

        $this->content->text .= "</div>";
        return $this->content;
    }

    /**
     * Get all blacklisted URLs for this block instance
     *
     * @return type
     */
    public function get_blacklist() {

        $blacklist = preg_split("/\n/", $this->config->blacklist, -1, PREG_SPLIT_NO_EMPTY);

        return $blacklist;
    }

    /*
     * Add search form
     *
     */
    public function get_user_search() {
        global $DB;

        if(!isset($_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'])) {

            $_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'] = optional_param('caboodlesearch', NULL, PARAM_ALPHANUM);

        } else if (strcmp($_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'], optional_param('caboodlesearch', NULL, PARAM_ALPHANUM)) != 0
                AND !is_null(optional_param('caboodlesearch', NULL, PARAM_ALPHANUM))) {

            $_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'] = optional_param('caboodlesearch', NULL, PARAM_ALPHANUM);
            unset($_SESSION['caboodle_usersearch_result'][$this->instance->id]['results']);

        }

        //var_dump($_SESSION['caboodle_usersearch_str'][$this->instance->id]);

        $search_str = $_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'];

        // get all resources
        $caboodle = new caboodle();
        $resources = $caboodle->get_resources();

        $this->content->text .= get_string('user_search_on', 'block_caboodle', $search_str);

        foreach ($resources as $resourceid => $resource) {
            if ($this->config->resource[$resourceid] == 1) {

                $this->content->text .= "<h4>" . $resource->name . "</h4>";

                if (empty($_SESSION['caboodle_usersearch_result'][$this->instance->id]['results'])) {

                    // execute
                    $results = $this->perform_search($resourceid, false, $search_str);

                    $_SESSION['caboodle_usersearch_result'][$this->instance->id]['results'] = $results;

                } else {
                    $results = $_SESSION['caboodle_usersearch_result'][$this->instance->id]['results'];
                }

                $this->content->text .= '<ul class="caboodle_results">';

                if (!empty($results)) {

                    foreach($results as $r => $result) {
                        $this->content->text .= '<li class="caboodle_results_item" style="margin: 3px 0;">';
                        $this->content->text .= '<a href="' . $result['url']  .'" target="_blank">' . $result['title'] . '</a>';
                        $this->content->text .= "</li>";
                    }

                } else {
                    // no results
                    $this->content->text .=  '<li>'. get_string('nothing_found', 'block_caboodle') . '</li>';
                }

                $this->content->text .= "</ul>";

            } // if
        } // foreach

    }

    /**
     * Return search form
     *
     * @global type $CFG
     * @global type $OUTPUT
     * @return string
     */
    public function get_search_form() {
        global $CFG, $OUTPUT;

        $strsearch  = get_string('search');
        $strgo      = get_string('go');
        $yourownsearch = get_string('yourownsearch', 'block_caboodle');
        //$advancedsearch = get_string('advancedsearch', 'block_caboodle');

        $text  = '<div class="searchform caboodle_searchform">';
        $text .= '<p>' . $yourownsearch . '</p>';
        $text .= '<form action="?" style="display:inline"><fieldset class="invisiblefieldset">';
        $text .= '<legend class="accesshide">'.$strsearch.'</legend>';
        $text .= '<input name="id" type="hidden" value="'.$this->page->course->id.'" />';  // course
        $text .= '<label class="accesshide" for="searchform_search">'.$strsearch.'</label>'.
                 '<input id="searchform_search" name="caboodlesearch" type="text" size="12" />';
        $text .= '<button id="searchform_button" type="submit" title="'.$strsearch.'">'.$strgo.'</button><br />';
        // advanced search
        //$text .= '<a href="'.$CFG->wwwroot.'/blocks/caboodle/search.php?id='.$this->page->course->id.'">'.$advancedsearch.'</a>';
        //$text .= $OUTPUT->help_icon('search');
        $text .= '</fieldset></form></div>';

        return $text;
    }

    public function perform_search($resourceid, $save = false, $search_str = null, $criteria_changed = false) {
        global $DB;
        
        if (is_null($search_str)) {
            if ($search_record = $DB->get_record('caboodle_search_results', array('instance' => $resourceid))) {
                $search_str = $search_record->searchstr;
            }
        }

        $numresults = get_config('caboodle', 'numresults');

        $sql = "SELECT r.name, rt.typeclass FROM {caboodle_resources} r, {caboodle_resource_types} rt
                 WHERE r.type = rt.id
                 AND r.id = ". $resourceid;
        $resource_data = $DB->get_record_sql($sql);

        $api_class_file = dirname(__FILE__) . '/lib_api/' .$resource_data->typeclass . ".php";
        $api_class = $resource_data->typeclass;

        // we don't need to check if file exists and/or is readable, below line will raise an error anyway
        // but the check can be added in the future to fail gracefully
        require_once($api_class_file);

        $api = new $api_class($resourceid, $this->instance->id, $numresults);

        $results = $api->search($search_str);

        if ($save) {
            $api->save_results();
        }
        
        // if a new search conducted - delete all items from blacklist (aka exclude list)
        if($criteria_changed) {
            $this->caboodle_clear_blacklist($this->instance->id);
        }

        return $results;
    }

    /**
     * Where this block may be added (only in course view)
     *
     * @return type
     */
    public function applicable_formats() {
        return array('all' => false,
                     'course-view' => true
                    );
    }

    /**
     * Allowing multiple instances of this block on a single page
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Yes, we have a config
     *
     * @return boolean
     */
    function has_config() { return true; }

    /**
     * Cron processing
     *
     * @global type $DB
     * @return boolean
     */
    public function cron() {
        global $DB;
        mtrace("block_caboodle cron");

        // get configuration options
        $numresults = get_config('caboodle', 'numresults');
        $removeafter = get_config('caboodle', 'removeafter');

        $caboodle = new caboodle();

        $instances = $caboodle->get_all_block_instances();

        foreach ($instances as $instanceid => $instance) {
            foreach ($instance->configdata->resource as $resourceid => $resource_enabled) {

                mtrace("Processing resource $resourceid for instance $instanceid ...");
                // resource needs to be enabled
                if ($resource_enabled) {
                    mtrace("\tResource is enabled!");

                    // we'll proceed only when there is a search query
                    if(!empty($instance->configdata->search)) {
                        mtrace("\tSearch query: " . $instance->configdata->search);

                        $sql = "SELECT rt.typeclass, r.type FROM {caboodle_resources} r, {caboodle_resource_types} rt
                                WHERE r.id = $resourceid
                                AND r.type = rt.id";

                        $resdata = $DB->get_record_sql($sql);

                        $api_class_file = dirname(__FILE__) . '/lib_api/' .$resdata->typeclass . ".php";
                        $api_class = $resdata->typeclass;

                        if (file_exists($api_class_file) && is_readable($api_class_file)) {
                            mtrace("\tExecuting API class: " . $resdata->typeclass);
                            require_once($api_class_file);

                            $api = new $api_class($resourceid, $instanceid, $numresults);

                            if (!$caboodle->is_expired($resourceid, $instanceid, $removeafter)) {

                                mtrace("\tExecuting search");
                                if ($search_result = $api->search($instance->configdata->search)) {

                                    $api->save_results();

                                } else {
                                    mtrace("Error: curl failed");

                                    if (isset($api->lasterror) && !empty($api->lasterror)) {
                                        mtrace("Last reported error: " . $api->lasterror);
                                    } else mtrace("No error string provided");

                                    // make sure cron will be executed on next run
                                    return false;
                                }

                                mtrace("\tDone searching");

                            } else {
                                mtrace("\tSearch results not expired yet, skipping search");
                            }

                        } else {
                            mtrace("\tError: API class does not exist or not readable: " . $api_class_file);
                            return false;
                        }

                    } // if

                } // if
            }// foreach
        } // foreach

        mtrace('Clean expired items...');
        if ($expired_items = $caboodle->get_all_expired_results($removeafter)) {

            foreach ($expired_items as $itemid => $itemdata) {
                $item = array('id' => $itemid);

                mtrace("Removing from caboodle_search_results item id " . $itemid);

                $DB->delete_records('caboodle_search_results', $item);
            }

        } else {
            mtrace('No expired items found');
        }


        return true;
    } // cron

    private function caboodle_clear_blacklist($id) {
        global $DB;
        
        if ($data = $DB->get_record('block_instances', array('id' => $id))) {
        
            $configdata = unserialize(base64_decode($data->configdata));


            $configdata->blacklist = '';

            $configdata = base64_encode(serialize($configdata));

            $record = new stdClass();
            $record->id = $id;
            $record->configdata = $configdata;

            $DB->update_record('block_instances', $record);

        
        } // if
        
    }
}
