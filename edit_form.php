<?php

class block_caboodle_edit_form extends block_edit_form
{

    protected function specific_definition($mform)
    {

        global $OUTPUT;

        // Section header title according to language file.
//        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // A sample string variable with a default value.
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_MULTILANG);

        $mform->addElement('header', 'general', get_string('resources', 'block_caboodle'));

        $repositories = array('Repository 1', 'Repository 2', 'Repository 3', 'Repository 4', 'Repository 5', 'Repository 6');

        foreach ($repositories as $k => $repository) {
            $mform->addElement('advcheckbox', "resource[$k]", $repository);
            $mform->setType('resource', PARAM_BOOL);
            $mform->addHelpButton('resource', 'langkey_help', 'block_caboodle');
        }

        $mform->addElement('header', 'general', get_string('search', 'block_caboodle'));
        $mform->addElement('text', 'search', get_string('search', 'block_caboodle'));

        $choices = array('Yes', 'No');
        $default = 0;
        $mform->addElement('select', 'student_search', get_string('student_search', 'block_caboodle'), $choices);
        $mform->setDefault('student_search', $default);
        $mform->setType('student_search', PARAM_ALPHA);
        $mform->addHelpButton('student_search', 'student_search_help', 'block_caboodle');

        $choices = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
        $default = 3;
        $mform->addElement('select', 'search_items_displayed', get_string('search_items_displayed', 'block_caboodle'), $choices);
        $mform->setDefault('search_items_displayed', $default);
        $mform->setType('search_items_displayed', PARAM_ALPHA);
        $mform->addHelpButton('search_items_displayed', 'search_items_displayed_help', 'block_caboodle');

        $mform->addElement('textarea', 'blacklist', get_string('blacklist', 'block_caboodle'), array('rows' => 6, 'cols' => 40));
        $mform->addHelpButton('blacklist', 'blacklist_help', 'block_caboodle');

        
        $mform->addElement('header', 'general', get_string('search_results', 'block_caboodle'));
        $cross = $OUTPUT->pix_icon('i/cross_red_small','blacklist');
        $mform->addElement('static', '', "<div>
        <h2>Repository 1 results</h2><ul style=\"list-style-type: none;\"><li>$cross search result 1 http://...</li><li>$cross search result 2 http://...</li><li>$cross search result 3 http://...</li><li>$cross search result 4 http://...</li></ul>
        <h2>Repository 2 results</h2><ul style=\"list-style-type: none;\"><li>$cross search result 1 http://...</li><li>$cross search result 2 http://...</li><li>$cross search result 3 http://...</li><li>$cross search result 4 http://...</li></ul>
         </div>", '');

    }
}
