<?php

defined('MOODLE_INTERNAL') || die();


class block_caboodle_edit_form extends block_edit_form
{

    protected function specific_definition($mform)
    {

        global $OUTPUT;

        // Section header title according to language file.
        // $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // A sample string variable with a default value.
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_MULTILANG);

        $mform->addElement('header', 'general', get_string('resources', 'block_caboodle'));

        $repositories = $this->get_resources();

        if (!empty($repositories)) {

            foreach ($repositories as $k => $repository) {
                $mform->addElement('advcheckbox', "config_resource_$k", $repository->name);
                $mform->setType("config_resource_$k", PARAM_BOOL);
                //$mform->addHelpButton("resource_$k", 'resource', 'block_caboodle');

            }

       } else { // if no resources found...

           // this is a temporary solution until adding interfaces feature is added
           global $DB;
           $record = new stdClass();
           $record->type = 0; // type SRU interface from Jisc MediaHub
           $record->name = "Jisc MediaHub (SRU interface)";
           $record->url = "http://m2m.edina.ac.uk/sru/mediahub";

           $DB->insert_record('caboodle_resources', $record);

           $repositories = $this->get_resources();

            foreach ($repositories as $k => $repository) {
                $mform->addElement('advcheckbox', "config_resource_$k", $repository->name);
                $mform->setType("config_resource_$k", PARAM_BOOL);
                //$mform->addHelpButton("resource_$k", 'resource', 'block_caboodle');

            }

            //$mform->addElement('static', '', '<h2>' . get_string('no_resources', 'block_caboodle') . '</h2>', '');


        } // else

        $mform->addElement('header', 'general', get_string('search', 'block_caboodle'));
        $mform->addElement('text', 'config_search', get_string('search', 'block_caboodle'));

        $choices = array(get_string('yes'), get_string('no'));
        $default = 0;
        $mform->addElement('select', 'config_student_search', get_string('student_search', 'block_caboodle'), $choices);
        $mform->setDefault('config_student_search', $default);
        $mform->setType('config_student_search', PARAM_ALPHA);
        $mform->addHelpButton('config_student_search', 'student_search', 'block_caboodle');

        $choices = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
        $default = 3;
        $mform->addElement('select', 'config_search_items_displayed', get_string('search_items_displayed', 'block_caboodle'), $choices);
        $mform->setDefault('config_search_items_displayed', $default);
        $mform->setType('config_search_items_displayed', PARAM_ALPHA);
        $mform->addHelpButton('config_search_items_displayed', 'search_items_displayed', 'block_caboodle');

        $mform->addElement('textarea', 'config_blacklist', get_string('blacklist', 'block_caboodle'), array('rows' => 6, 'cols' => 40));
        $mform->addHelpButton('config_blacklist', 'blacklist', 'block_caboodle');

        $mform->addElement('header', 'general', get_string('search_results', 'block_caboodle'));
        $cross = $OUTPUT->pix_icon('i/cross_red_small','blacklist');

        foreach ($repositories as $k => $repository) {

            $mform->addElement('html', '', "<div><h2>".$repository->name."</h2>");

            
//            <ul style=\"list-style-type: none;\">
//              <li>$cross search result 1 http://...</li>
//              <li>$cross search result 2 http://...</li>
//              <li>$cross search result 3 http://...</li>
//              <li>$cross search result 4 http://...</li>
//            </ul>

            $mform->addElement('html', '', "</div>");
        }

    }

    /**
     *
     * @global resource $DB
     * @return array
     */
    public function get_resources() {
        global $DB;

        $resources = $DB->get_records('caboodle_resources', array(), null, 'id,name');

        return $resources;
    }

}
