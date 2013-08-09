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

require_once($CFG->dirroot . '/blocks/caboodle/lib.php');

class block_caboodle_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $OUTPUT, $PAGE, $CFG;

        // an "X" before blacklisted urls
        $cross = $OUTPUT->pix_icon('i/cross_red_small','blacklist');

        // get js with Base64 encode/deocde class
        $PAGE->requires->js('/blocks/caboodle/js/base64-encode.js');
        $PAGE->requires->js('/blocks/caboodle/js/initialsearch.js');
        // add js which do automatic blacklisting
        $PAGE->requires->yui_module('moodle-block_caboodle-blacklister', 'M.block_caboodle.init_blacklister');
        
        // disable form change check for "initial search"
        $mform->disable_form_change_checker();

        $mform->addElement('header', 'general', get_string('resources', 'block_caboodle'));

        $caboodle = new caboodle();
        $repositories = $caboodle->get_resources();

        $mform->addElement('html', '<div id="caboodle_repositories">');
        
        foreach ($repositories as $k => $repository) {
            $mform->addElement('advcheckbox', "config_resource[$k]", $repository->name);
            $mform->setType("config_resource[$k]", PARAM_BOOL);
        }
        
        $mform->addElement('html', '</div>');

        $mform->addElement('header', 'general', get_string('search', 'block_caboodle'));

        // get initial search count
        $initialsearch = optional_param('initialsearchcnt', '', PARAM_RAW);

        // if initial search string is different than prevous one, set initialsearchcnt
        // this will clear the exclude list if search string is different than prevous one
        if (strlen(optional_param('caboodle_initialsearch', '', PARAM_RAW)) != 0 && strcmp($initialsearch, optional_param('caboodle_initialsearch', '', PARAM_RAW)) != 0 ) {
                $initialsearch = '&initialsearchcnt=' . optional_param('caboodle_initialsearch', '', PARAM_RAW);

        } else {
            $initialsearch = '&initialsearchcnt=' . $initialsearch;
        }
        
        $button_url = $CFG->wwwroot . '/course/view.php?id=' . required_param('id', PARAM_INT) . '&sesskey=' . required_param('sesskey', PARAM_ALPHANUM);
        $button_url .= '&bui_editid=' . required_param('bui_editid', PARAM_INT) . $initialsearch . '&caboodle_initialsearch=';
        
        // config_search button attributes with additional js/style
        $config_search_attributes = array('onkeydown' => "return in_page_search(event, '" . $button_url ."');", "style" => "margin-left: 0;");
        // add config_search text box
        $mform->addElement('text', 'config_search', get_string('search', 'block_caboodle'), $config_search_attributes);

        // button code - see js/initialsearch.js file
        $button = '<input name="intro" value="'
                . get_string('initial_search', 'block_caboodle')
                . '" type="button" id="id_intro" onClick="document.location.href=\'' . $button_url
                . '\' + buttonUrl();"/>';
        // add button as a static element
        $mform->addElement('static', 'initialsearch', '', $button);

        $choices = array(get_string('no'), get_string('yes'));
        $default = optional_param('student_option', 1, PARAM_INT);
        $mform->addElement('select', 'config_student_search', get_string('student_search', 'block_caboodle'), $choices);
        $mform->setDefault('config_student_search', $default);
        $mform->setType('config_student_search', PARAM_BOOL);
        $mform->addHelpButton('config_student_search', 'student_search', 'block_caboodle');

        // just to be sure it'll not be reused by mistake
        unset($choices);

        for ($choice=1; $choice < 11; $choice++) {
            $choices[$choice] = $choice;
        }

        $temp_num_items = optional_param('number_items', 0, PARAM_INT);
        $mform->addElement('select', 'config_search_items_displayed', get_string('search_items_displayed', 'block_caboodle'), $choices);
        $mform->setDefault('config_search_items_displayed', $default);
        if (!$temp_num_items) {
            $mform->setDefault('config_search_items_displayed', 3);
        } else {
            $mform->setConstant('config_search_items_displayed', $temp_num_items);
        }
        $mform->setType('config_search_items_displayed', PARAM_INT);
        $mform->addHelpButton('config_search_items_displayed', 'search_items_displayed', 'block_caboodle');


        if (isset($_GET['caboodle_initialsearch'])) {
            
            if (strlen(optional_param('blacklisted', '', PARAM_RAW)) == 0 
                    || strcmp(optional_param('initialsearchcnt', '', PARAM_RAW), optional_param('caboodle_initialsearch', '', PARAM_RAW)) != 0  
                    || strlen(optional_param('caboodle_initialsearch', '', PARAM_RAW)) == 0) {
                $blacklist = array();
            } else {

                // decode all elements
                $blacklist_tmp = base64_decode(urldecode(optional_param('blacklisted', false, PARAM_RAW)));
                // array-ify them and trim all wite spaces from the begginning and end of each string
                $blacklist = $caboodle->trim_array_elements(preg_split("/\n/", $blacklist_tmp, -1, PREG_SPLIT_NO_EMPTY));
            }
            
        } else {
            
                $blacklist = $caboodle->trim_array_elements(preg_split("/\n/", $this->block->config->blacklist, -1, PREG_SPLIT_NO_EMPTY));
            
        }
        
        // prepare unordered list for blacklist
        $blacklist_ul = '<ul class="caboodle_blacklisted" style="list-style-type: none;">';
        
        if (count($blacklist) > 0) {

            foreach ($blacklist as $index => $url) {

                $url = explode('::', $url);
                
                // TODO: data validation
                $blacklist_ul .= '<li class="caboodle_blacklisted_item" style="margin: 3px 0;">' . $cross . '&nbsp;';
                $blacklist_ul .= '<a href="' . $url[1]  .'" target="_blank">' . $url[0] .'</a> (' . $url[1] . ')';
                $blacklist_ul .= '</li>';
            }
        } else {
            $blacklist_ul .= '<li id="blacklist_empty">' . get_string('blacklist_empty', 'block_caboodle') . '</li>';
        }

        $blacklist_ul .= '</ul>';

        $mform->addElement('static', 'blacklist', get_string('blacklist', 'block_caboodle'), $blacklist_ul);
        $mform->addHelpButton('blacklist', 'blacklist', 'block_caboodle');

        $mform->addElement('textarea', 'config_blacklist', '', array('rows' => 10, 'cols' => 140, 'hidden' => 'hidden', 'style' => 'display: none;'));
        //$mform->addElement('textarea', 'config_blacklist', '', array('rows' => 10, 'cols' => 140));

        $mform->addElement('header', 'general', get_string('search_results', 'block_caboodle'));

        $caboodle = new caboodle();

        foreach ($repositories as $k => $repository) {

            // if resource enabled, display it:
            if ($this->block->config->resource[$k] == 1 || optional_param('caboodle_initialsearch', false, PARAM_RAW)) {
                $mform->addElement('html', '<div class="caboodle_results_settings"><h2>'.$repository->name."</h2>");

                // if initial search not set, retrieve saved results
                if (! isset($_GET['caboodle_initialsearch'])) {
                    // check if resource has any search results
                    $results = $caboodle->get_results($k, $this->block->instance->id);
                } else {
                    // if initial search string set and repo checked, perform search
                    if (optional_param('repo_'.$k, 0, PARAM_INT) == 1 && strlen(optional_param('caboodle_initialsearch', '', PARAM_RAW)) > 0) {
                        $results = $this->caboodle_perform_search($k);
                    }
                    
                }

                // get urls from prevously retrieved blacklist
                $blacklist = $caboodle->get_urls_from_blacklist($blacklist);

                if (!empty($results)) {

                    $mform->addElement('html', '<ul class="caboodle_blacklister" id="repo_'.$k.'" style="list-style-type: none;">');
                    
                    
                    foreach($results as $result_id => $result_data) {

                        // filter out blacklisted urls
                        if (!in_array($result_data['url'], $blacklist)) {

                            $mform->addElement('html', '<li class="caboodle_blacklister_item" style="margin: 3px 0;">' . $cross . '&nbsp;');
                            $mform->addElement('html', '<a href="' . $result_data['url']  .'" target="_blank">' . $result_data['title'] .'</a>' . ' (' . $result_data['url'] . ')' );
                            $mform->addElement('html', '</li>');

                        }

                    } // foreach results

                    $mform->addElement('html', '</ul>');

                } else if ( isset($_GET['caboodle_initialsearch']) && (optional_param('repo_'.$k, 0, PARAM_INT) == 0 || strlen(optional_param('caboodle_initialsearch', '', PARAM_RAW)) == 0) ) {
                    // repository disabled this time
                    $mform->addElement('html', '<ul class="caboodle_blacklister" style="list-style-type: none;">');
                    $mform->addElement('html', '<li class="caboodle_blacklister_item">'. get_string('repository_disabled', 'block_caboodle') . '</li>');
                    $mform->addElement('html', '</ul>');
                    
                } else {
                    // nothing found
                    $mform->addElement('html', '<ul class="caboodle_blacklister" style="list-style-type: none;">');
                    $mform->addElement('html', '<li class="caboodle_blacklister_item">'. get_string('nothing_found', 'block_caboodle') . '</li>');
                    $mform->addElement('html', '</ul>');
                }
                
                $mform->addElement('html', "</div>");
            } // if
        } // foreach

    }

    /**
     * Perform search on resourceid set API and return results (max = 20)
     *
     * @todo Move this method to caboodle class (in lib)
     *
     * @global resource $DB
     * @param int $resourceid
     * @return array
     */
    private function caboodle_perform_search($resourceid) {
        global $DB;

        $search_str = optional_param('caboodle_initialsearch', false, PARAM_RAW);

        $sql = "SELECT r.name, rt.typeclass FROM {caboodle_resources} r, {caboodle_resource_types} rt
                 WHERE r.type = rt.id
                 AND r.id = ". $resourceid;
        $resource_data = $DB->get_record_sql($sql);

        $api_class_file = dirname(__FILE__) . '/lib_api/' .$resource_data->typeclass . ".php";
        $api_class = $resource_data->typeclass;

        // we don't need to check if file exists and/or is readable, below line will raise an error anyway
        // but the check can be added in the future to fail gracefully
        require_once($api_class_file);

        $api = new $api_class($resourceid, $this->block->instance->id);

        $results = $api->search($search_str);

        return $results;
    } // caboodle_perform_search
    

    public function definition_after_data() {
        
        parent::definition_after_data();
        
        $mform =& $this->_form;
        $search_term = optional_param('config_search', NULL, PARAM_RAW);
        $blacklist = urldecode(optional_param('blacklisted', '', PARAM_RAW));
        $search_id = required_param('bui_editid', PARAM_INT);

        if (!is_null($search_term)) {
            $_SESSION['search_id'] = $search_id;
            $_SESSION['search_term'] = $search_term;
        }
        
        // set student option Yes/No
        if (!is_null(optional_param('student_option', NULL, PARAM_INT))) {
            $config_student_option =& $mform->getElement('config_student_search');
            $config_student_option->_values[0] = optional_param('student_option', NULL, PARAM_INT);
        }
        
        // override default value if initial search executed
        if (!is_null(optional_param('caboodle_initialsearch', NULL, PARAM_RAW))) {
            // set search string
            $config_search =& $mform->getElement('config_search');
            $config_search->_attributes['value'] = optional_param('caboodle_initialsearch', '', PARAM_RAW);
            
            // check all resources as enabled
            $caboodle = new caboodle();
            $repositories = $caboodle->get_resources();

            foreach ($repositories as $k => $repo) {
                $config_resource[$k] =& $mform->getElement('config_resource[' . $k . ']');
                
                if (optional_param('repo_'.$k, false, PARAM_INT) == 1) {
                    $config_resource[$k]->_attributes['checked'] = 'checked';
                } else {
                    unset($config_resource[$k]->_attributes['checked']);
                    // clean blacklist (whole blacklist, it should be added logic to clean out only repository items)
                    $config_blacklist =& $mform->getElement('config_blacklist');
                    $config_blacklist->_value = '';
                }
            } // foreach
            
            // blacklist
            
            if (optional_param('initialsearchcnt', 0, PARAM_INT) == 0 ) {
                $config_blacklist =& $mform->getElement('config_blacklist');
                $config_blacklist->_value = '';
            } else {
                $blacklist_decoded = base64_decode($blacklist);
                $config_blacklist =& $mform->getElement('config_blacklist');
                $config_blacklist->_value = $blacklist_decoded;
            }
            
        } // if
        
        
    } // definition_after_data

}
