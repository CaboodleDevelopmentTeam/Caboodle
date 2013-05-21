<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/caboodle/lib.php');

class block_caboodle_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $OUTPUT, $PAGE, $CFG;

        // add js which do automatic blacklisting
        $PAGE->requires->yui_module('moodle-block_caboodle-blacklister', 'M.block_caboodle.init_blacklister');

        // A sample string variable with a default value.
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_MULTILANG);

        $mform->addElement('header', 'general', get_string('resources', 'block_caboodle'));

        $caboodle = new caboodle();
        $repositories = $caboodle->get_resources();

        foreach ($repositories as $k => $repository) {
            $mform->addElement('advcheckbox', "config_resource[$k]", $repository->name);
            $mform->setType("config_resource[$k]", PARAM_BOOL);
        }

        $mform->addElement('header', 'general', get_string('search', 'block_caboodle'));
        $mform->addElement('text', 'config_search', get_string('search', 'block_caboodle'));

        $choices = array(get_string('no'), get_string('yes'));
        $default = 0;
        $mform->addElement('select', 'config_student_search', get_string('student_search', 'block_caboodle'), $choices);
        $mform->setDefault('config_student_search', $default);
        $mform->setType('config_student_search', PARAM_BOOL);
        $mform->addHelpButton('config_student_search', 'student_search', 'block_caboodle');

        $choices = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
        $default = 3;
        $mform->addElement('select', 'config_search_items_displayed', get_string('search_items_displayed', 'block_caboodle'), $choices);
        $mform->setDefault('config_search_items_displayed', $default);
        $mform->setType('config_search_items_displayed', PARAM_INT);
        $mform->addHelpButton('config_search_items_displayed', 'search_items_displayed', 'block_caboodle');

        $mform->addElement('textarea', 'config_blacklist', get_string('blacklist', 'block_caboodle'), array('rows' => 6, 'cols' => 40));
        $mform->addHelpButton('config_blacklist', 'blacklist', 'block_caboodle');

        $mform->addElement('header', 'general', get_string('search_results', 'block_caboodle'));
        $cross = $OUTPUT->pix_icon('i/cross_red_small','blacklist');

        $caboodle = new caboodle();

        foreach ($repositories as $k => $repository) {

            // if resource enabled, display it:
            if ($this->block->config->resource[$k] == 1) {
                $mform->addElement('html', "<div><h2>".$repository->name."</h2>");

                // check if resource has any search results
                $results = $caboodle->get_results($k, $this->block->instance->id);

                if (!empty($results)) {

                    $mform->addElement('html', '<ul style="list-style-type: none;">');

                    foreach($results as $result_id => $result_data) {

                        $mform->addElement('html', '<li style="margin: 3px 0;">');
                        $mform->addElement('html', '<a href="' . $result_data['url']  .'">' . $result_data['title'] .'</a>' . ' (' . $result_data['url'] . ')' );
                        $mform->addElement('html', '</li>');
//                  <li>$cross search result 1 http://...</li>

                    }

                    $mform->addElement('html', '</ul>');

                } else {
                    // nothing found
                    $mform->addElement('html', '<ul style="list-style-type: none;">');
                    $mform->addElement('html', '<li>'. get_string('nothing_found', 'block_caboodle') . '</li>');
                    $mform->addElement('html', '</ul>');
                }

                $mform->addElement('html', "</div>");
            } // if
        } // foreach

    }

}
