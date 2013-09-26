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

        // get js for htmldump
        $this->page->requires->js('/blocks/caboodle/js/htmldump.js');

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

        // show search form and results if user search is enabled
        if ($this->config->student_search) {
            
            // show user search results (if any)
            if (isset($_SESSION['caboodle_usersearch_result'][$this->instance->id]) || !is_null(optional_param('caboodleusersearch', NULL, PARAM_RAW)) ) {
                $usersearch = $this->get_user_search();
            }
            
            $this->content->text .= $this->get_search_form();
            
            if (!empty($usersearch)) {
                $this->content->text .= $usersearch;
            }
        } // student search if

        if (array_key_exists('search_term', $_SESSION) && array_key_exists('search_id', $_SESSION) && $_SESSION['search_id'] == $this->instance->id) {
            $search_str = $_SESSION['search_term'];
            unset($_SESSION['search_term']);
            unset($_SESSION['search_id']);
        } else {
            $search_str = $this->config->search;
        }
        
        if (!empty($search_str)) {
            // get all resources
            $caboodle = new caboodle();
            $resources = $caboodle->get_resources();

            $this->content->text .= get_string('search_on', 'block_caboodle', $search_str);
            //$caboodle_results = get_string('search_on', 'block_caboodle', $search_str);
            $caboodle_results = '';

            foreach ($resources as $resourceid => $resource) {
                if ($this->config->resource[$resourceid] == 1) {

                    if (!empty($resource->repository_url)) {

                        $resource_name = '<a href="' . $resource->repository_url . '" target="_blank">' . $resource->name . '</a>';

                    } else {
                        $resource_name = $resource->name;
                    }

                    $caboodle_results .= "<h4>" . $resource_name.  "</h4>";

                    $results = $caboodle->get_results($resourceid, $this->instance->id);

                    // if results are empty, no search has been performed yet, we can do that now
                    if (empty($results)) {
                        $results = $this->perform_search($resourceid, true, $search_str);
                    }

                    $caboodle_results .= '<ul class="caboodle_results">';

                    if (!empty($results)) {
                        // get search string saved in DB
                        $old_search_str = $caboodle->get_search_string($resourceid, $this->instance->id);

                        // check if search string in DB has ben updated with the one in configuration
                        if (strcmp($search_str, $old_search_str) == 0) {

                            // get and filter blacklist urls
                            $blacklist = $caboodle->trim_array_elements($this->get_blacklist());
                            $blacklist = $caboodle->get_urls_from_blacklist($blacklist);
                            $count = 0;

                            // display list of elements
                            foreach ($results as $rid => $rdata) {

                                if (!in_array($rdata['url'], $blacklist) && $count < $this->config->search_items_displayed) {

                                    $href = '<a href="' . $rdata['url']  .'" target="_blank">' . $rdata['title'] . '</a>';

                                    if ($this->page->user_is_editing()) {
                                        
                                        $href_encoded = urlencode(base64_encode(urlencode($href)));
                                        $plus = $OUTPUT->pix_icon('t/switch_plus', get_string('plus_href', 'block_caboodle'), '',
                                                array('style' => 'margin-right: 5px; display: inline;', 'onclick' => 'htmlDump(' . required_param('id', PARAM_INT) .', "' . $href_encoded . '");'));

                                    } else {

                                        $plus = '';
                                        
                                    }

                                    $caboodle_results .= '<li class="caboodle_results_item" style="margin: 3px 0;">';
                                    $caboodle_results .= $plus . $href;
                                    $caboodle_results .= "</li>";
                                    $count++;
                                } // if

                            } // foreach

                            $caboodle_results .= "</ul>";

                        } else {

                            // search string has changed, execute search and save all data
                            $results = $this->perform_search($resourceid, true, $search_str, true);

                            $caboodle_results .= '<ul class="caboodle_results">';

                            // new search criteria, clear black list
                            $blacklist = $caboodle->trim_array_elements($this->get_blacklist());
                            $count = 0;

                            // display list of elements
                            foreach ($results as $rid => $rdata) {

                                if (!in_array($rdata['url'], $blacklist) && $count < $this->config->search_items_displayed) {

                                    $href = '<a href="' . $rdata['url']  .'" target="_blank">' . $rdata['title'] . '</a>';

                                    if ($this->page->user_is_editing()) {

                                        $href_encoded = urlencode(base64_encode(urlencode($href)));
                                        $plus = $OUTPUT->pix_icon('t/switch_plus', get_string('plus_href', 'block_caboodle'), '',
                                                array('style' => 'margin-right: 5px; display: inline;', 'onclick' => 'htmlDump(' . required_param('id', PARAM_INT) .', "' . $href_encoded . '");'));

                                    } else {

                                        $plus = '';

                                    }

                                    $caboodle_results .= '<li class="caboodle_results_item" style="margin: 3px 0;">';
                                    $caboodle_results .= $plus . $href;
                                    $caboodle_results .= "</li>";
                                    $count++;
                                } // if

                            } // foreach

                            $caboodle_results .= "</ul>";
                        }

                    } else {
                        // no results
                        $caboodle_results .= '<ul class="caboodle_results">';
                        $caboodle_results .=   '<li class="caboodle_results_item">'. get_string('nothing_found', 'block_caboodle') . '</li>';
                        $caboodle_results .= "</ul>";
                    }

                } // if resource is enabled
            } // foreach resources

            $this->content->text .= $caboodle_results;

        } else {
            // no search string
            $this->content->text .= '<h3>'. get_string('nosearchstring', 'block_caboodle') . '</h3>';

            // clear exclude (aka black) list
            $this->caboodle_clear_blacklist($this->instance->id);
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
        $text = '';

        // return nothing if id is not match for this block or there is no saved user search
        if ((isset($_GET['caboodle_block_id']) && optional_param('caboodle_block_id', 0, PARAM_INT) != (int)$this->instance->id)
                && !isset($_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'])) {
            return '';
        }

        // user search is for this block? clean possible previously saved results
        if (!is_null(optional_param('caboodleusersearch', null, PARAM_RAW)) && optional_param('caboodle_block_id', 0, PARAM_INT) == $this->instance->id) {
            $_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'] = optional_param('caboodleusersearch', null, PARAM_RAW);
            $_SESSION['caboodle_usersearch_result'][$this->instance->id]['results'] = "";
        }

        // a new user search for this block? clean possible previously saved results
        if (isset($_GET['caboodleusersearch']) &&
                strcmp($_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'], optional_param('caboodleusersearch', null, PARAM_RAW)) != 0) {
            $_SESSION['caboodle_usersearch_result'][$this->instance->id]['results'] = "";
        }
        
        if (isset($_GET['caboodleusersearch']) && strlen(optional_param('caboodleusersearch', null, PARAM_RAW)) == 0) {
            unset($_SESSION['caboodle_usersearch_result'][$this->instance->id]['search']);
            unset($_SESSION['caboodle_usersearch_result'][$this->instance->id]['results']);
            return $text;
        }

        $search_str = $_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'];

        // get all resources
        $caboodle = new caboodle();
        $resources = $caboodle->get_resources();

        $text .= get_string('user_search_on', 'block_caboodle', $search_str);

        if (empty($_SESSION['caboodle_usersearch_result'][$this->instance->id]['results'])) {

            // execute search
            // detect php
            // $php = shell_exec('which php');
            // just exec php and let the system worry about it
            $php = 'php';
            //$php = '/opt/php/bin/php -c /opt/conf/php.ini';
            $exec = $php . ' ' . dirname(__FILE__) . '/cli/usersearch.php ' . $this->instance->id .
                    ' ' . $this->config->search_items_displayed . ' "' . $search_str . '"';
            
            $shell_results = shell_exec($exec);

            $shell_results = preg_split("/\n/", $shell_results, -1, PREG_SPLIT_NO_EMPTY);

            // empty results array
            $results = array();

            foreach ($shell_results as $r => $result_chunk) {

                // decode results
                $decoded_results = json_decode($result_chunk, true);

                if (!is_null($decoded_results)) {
                    // get keys
                    $keys = array_keys($decoded_results);

                    // make sure that it all be saved in one array without overwriting it (well unless keys are the same which shouldn't happen)
                    $results[$keys[0]] = $decoded_results[$keys[0]];
                } // if

            } // foreach

            $_SESSION['caboodle_usersearch_result'][$this->instance->id]['results'] = $results;

        } else {
            $results = $_SESSION['caboodle_usersearch_result'][$this->instance->id]['results'];
        }
        // foreach resources!
        foreach ($resources as $resid => $resource) {
            
            //if this specific resource is activated in the settings
            if ($this->config->resource[$resid] == 1){
                $text .= "<h4>" . $resource->name . "</h4>";
                $text .= '<ul class="caboodle_results">';
            }

            if (!empty($results[$resid])) {

                $count = 0;

                foreach($results[$resid] as $r => $result) {

                    // display only configured amount of items
                    if ($count < $this->config->search_items_displayed) {
                        $text .= '<li class="caboodle_results_item" style="margin: 3px 0;">';
                        $text .= '<a href="' . $result['url']  .'" target="_blank">' . $result['title'] . '</a>';
                        $text .= "</li>";
                    }
                    
                    $count++;
                } // foreach

            } else {
                // no results
                if ($this->config->resource[$resid] == 1){
                    $text .=  '<li>'. get_string('nothing_found', 'block_caboodle') . '</li>';
                }
            }

            $text .= "</ul>";
        } // foreach

        return $text;
    } // get_user_search

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
        $text .= '<input name="id" type="hidden" value="'.$this->page->course->id.'" />';  // course id
        $text .= '<input name="caboodle_block_id" type="hidden" value="'.$this->instance->id.'" />';  // block id
        $text .= '<label class="accesshide" for="searchform_search">'.$strsearch.'</label>';
        
        if (!empty($_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'])) {
            $text .= '<input id="searchform_search" name="caboodleusersearch" type="text" size="12" value="'.
                    $_SESSION['caboodle_usersearch_result'][$this->instance->id]['search'] .'"/>';
        } else {
            $text .= '<input id="searchform_search" name="caboodleusersearch" type="text" size="12" />';
        }
        
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
                                $search_result = $api->search($instance->configdata->search);
                                $api->save_results();

                                if (strlen($search_result) == 0) {
                                    mtrace("Notice: search results empty, see below messages");

                                    if (isset($api->lasterror) && !empty($api->lasterror)) {
                                        mtrace("Last reported cURL error: " . $api->lasterror);
                                    } else mtrace("No problems reported by cURL");

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

    /**
     * Specialization is being called just after init()
     * We are using it for setting block title
     * 
     */
    public function specialization() {

        if (!empty($this->config->title)) {

          $this->title = $this->config->title;

        } else {

          $this->config->title = get_string('pluginname', 'block_caboodle');

        }

    } // specialization


}
