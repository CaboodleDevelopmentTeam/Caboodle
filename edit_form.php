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

        // add js which do automatic blacklisting
        $PAGE->requires->yui_module('moodle-block_caboodle-blacklister', 'M.block_caboodle.init_blacklister');
        $PAGE->requires->js('/blocks/caboodle/js/main.js');

        $mform->addElement('header', 'general', get_string('resources', 'block_caboodle'));

        $caboodle = new caboodle();
        $repositories = $caboodle->get_resources();

        foreach ($repositories as $k => $repository) {
            $mform->addElement('advcheckbox', "config_resource[$k]", $repository->name);
            $mform->setType("config_resource[$k]", PARAM_BOOL);
        }

        $mform->addElement('header', 'general', get_string('search', 'block_caboodle'));
        $mform->addElement('text', 'config_search', get_string('search', 'block_caboodle'));
        $mform->setDefault('config_search', optional_param('caboodle_initialsearch', '', PARAM_ALPHANUM));


        $button_url = $CFG->wwwroot . '/course/view.php?id=' . required_param('id', PARAM_INT) . '&sesskey=' . required_param('sesskey', PARAM_ALPHANUM);
        $button_url .= '&bui_editid=' . required_param('bui_editid', PARAM_INT) . '&caboodle_initialsearch=';

        // button code
        // buttonUrl js function can be found at the end of yui/blacklister/blacklister.js file
        $button = '<input name="intro" value="'
                . get_string('initial_search', 'block_caboodle')
                . '" type="button" id="id_intro" onClick="document.location.href=\'' . $button_url
                . '\' + buttonUrl();"/>';
        // add button as a static element
        $mform->addElement('static', 'initialsearch', '', $button);

        $choices = array(get_string('no'), get_string('yes'));
        $default = 1;
        $mform->addElement('select', 'config_student_search', get_string('student_search', 'block_caboodle'), $choices);
        $mform->setDefault('config_student_search', $default);
        $mform->setType('config_student_search', PARAM_BOOL);
        $mform->addHelpButton('config_student_search', 'student_search', 'block_caboodle');

        // just to be sure it'll not be reused by mistake
        unset($choices);

        for ($choice=1; $choice < 11; $choice++) {
            $choices[$choice] = $choice;
        }

        $default = 3;
        $mform->addElement('select', 'config_search_items_displayed', get_string('search_items_displayed', 'block_caboodle'), $choices);
        $mform->setDefault('config_search_items_displayed', $default);
        $mform->setType('config_search_items_displayed', PARAM_INT);
        $mform->addHelpButton('config_search_items_displayed', 'search_items_displayed', 'block_caboodle');

        $blacklist = $caboodle->trim_array_elements(preg_split("/\n/", $this->block->config->blacklist, -1, PREG_SPLIT_NO_EMPTY));

        $blacklist_ul = '<ul class="caboodle_blacklisted" style="list-style-type: none;">';

        if (count($blacklist) > 0) {

            foreach ($blacklist as $index => $url) {

                $blacklist_ul .= '<li class="caboodle_blacklisted_item" style="margin: 3px 0;">' . $cross . '&nbsp;';
                $blacklist_ul .= '<a href="' . $url  .'">' . $url .'</a>';
                $blacklist_ul .= '</li>';
            }
        }

        $blacklist_ul .= '</ul>';

        $mform->addElement('static', 'blacklist', get_string('blacklist', 'block_caboodle'), $blacklist_ul);
        $mform->addHelpButton('blacklist', 'blacklist', 'block_caboodle');

        $mform->addElement('textarea', 'config_blacklist', '', array('rows' => 10, 'cols' => 140, 'hidden' => 'hidden'));
        //$mform->addElement('textarea', 'config_blacklist', '', array('rows' => 10, 'cols' => 140));

        $mform->addElement('header', 'general', get_string('search_results', 'block_caboodle'));

        $caboodle = new caboodle();

        foreach ($repositories as $k => $repository) {

            // if resource enabled, display it:
            if ($this->block->config->resource[$k] == 1 OR optional_param('caboodle_initialsearch', false, PARAM_ALPHANUM)) {
                $mform->addElement('html', '<div class="caboodle_results_settings"><h2>'.$repository->name."</h2>");

                // if initial search not set, retrieve saved results
                if (! optional_param('caboodle_initialsearch', false, PARAM_ALPHANUM)) {
                    // check if resource has any search results
                    $results = $caboodle->get_results($k, $this->block->instance->id);
                } else {
                    // if initial search string set, perform search
                    $results = $this->caboodle_perform_search($k);
                }

                $blacklist = $caboodle->trim_array_elements(preg_split("/\n/", $this->block->config->blacklist, -1, PREG_SPLIT_NO_EMPTY));

                if (!empty($results)) {

                    $mform->addElement('html', '<ul class="caboodle_blacklister" style="list-style-type: none;">');

                    foreach($results as $result_id => $result_data) {

                        // filter out blacklisted urls
                        if (!in_array($result_data['url'], $blacklist)) {

                            $mform->addElement('html', '<li class="caboodle_blacklister_item" style="margin: 3px 0;">' . $cross . '&nbsp;');
                            $mform->addElement('html', '<a href="' . $result_data['url']  .'">' . $result_data['title'] .'</a>' . ' (' . $result_data['url'] . ')' );
                            $mform->addElement('html', '</li>');

                        }

                    } // foreach results

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

        $search_str = optional_param('caboodle_initialsearch', false, PARAM_ALPHANUM);

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

}
