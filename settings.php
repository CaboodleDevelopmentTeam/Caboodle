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


defined('MOODLE_INTERNAL') || die();


//$settings->add(new admin_setting_heading('sampleheader',
//                                         get_string('headerconfig', 'block_caboodle'),
//                                         get_string('descconfig', 'block_caboodle')));

$settings->add(new admin_setting_configtext('caboodle/numresults',
                                            get_string('label_results', 'block_caboodle'),
                                            get_string('desc_results', 'block_caboodle'),
                                            20, // default value
                                            PARAM_INT
                                            ));

$settings->add(new admin_setting_configtext('caboodle/removeafter',
                                            get_string('label_removecache', 'block_caboodle'),
                                            get_string('desc_removecache', 'block_caboodle'),
                                            43200, // default vaule 43200 seconds = 12h
                                            PARAM_INT
                                            ));

$settings->add(new admin_setting_configtext('caboodle/php_path',
                                            get_string('label_phppath', 'block_caboodle'),
                                            get_string('desc_phppath', 'block_caboodle'),
                                            'php', // default value
                                            PARAM_RAW
                                            ));

$settings->add(new admin_setting_configtext('caboodle/timeout',
                                            get_string('label_timeout', 'block_caboodle'),
                                            get_string('desc_timeout', 'block_caboodle'),
                                            10000, // default value
                                            PARAM_INT
                                            ));




$settings->add(new admin_setting_heading('ebscoheader',
                                         get_string('ebscoheaderconfig', 'block_caboodle'),
                                         get_string('ebscodescconfig', 'block_caboodle')));

$settings->add(new admin_setting_configtext('caboodle/ebscoprof',
                                            get_string('label_ebscoprof', 'block_caboodle'),
                                            get_string('desc_ebscoprof', 'block_caboodle'),
                                            '', // default value
                                            PARAM_RAW
                                            ));

$settings->add(new admin_setting_configtext('caboodle/ebscopwd',
                                            get_string('label_ebscopwd', 'block_caboodle'),
                                            get_string('desc_ebscopwd', 'block_caboodle'),
                                            '', // default value
                                            PARAM_ALPHANUM
                                            ));

$settings->add(new admin_setting_configtextarea('caboodle/ebscodb',
                                            get_string('label_ebscodb', 'block_caboodle'),
                                            get_string('desc_ebscodb', 'block_caboodle'),
                                            '', // default value
                                            PARAM_RAW
                                            ));


$settings->add(new admin_setting_heading('proquest_header',
                                         get_string('proquest_headerconfig', 'block_caboodle'),
                                         get_string('proquest_descconfig', 'block_caboodle')));

$settings->add(new admin_setting_configtext('caboodle/proquest_username',
                                            get_string('label_proquestusername', 'block_caboodle'),
                                            get_string('desc_proquestusername', 'block_caboodle'),
                                            '', // default value
                                            PARAM_ALPHANUM
                                            ));

$settings->add(new admin_setting_configtext('caboodle/proquest_pwd',
                                            get_string('label_proquestpwd', 'block_caboodle'),
                                            get_string('desc_proquestpwd', 'block_caboodle'),
                                            '', // default value
                                            PARAM_ALPHANUM
                                            ));
