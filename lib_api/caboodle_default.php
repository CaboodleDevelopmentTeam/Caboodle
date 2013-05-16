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

require_once(dirname(__FILE__) . 'locallib.php');

/**
 * Class caboodle_default returns static query results for testing purposes
 * It may be extended to use real API in the future
 */
class caboodle_default extends caboodle_api {

    /**
     * This method returns static search result
     *
     * @param type $query
     * @return array
     */
    public function search($query) {

        $ret = array(
            0 => array(
                'title' => 'Title 1',
                'url'   => 'http://localhost'
            ),
            1 => array(
                'title' => 'Title 2',
                'url'   => 'http://localhost'
            ),
            2 => array(
                'title' => 'Title 3',
                'url'   => 'http://localhost'
            ),
            3 => array(
                'title' => 'Title 4',
                'url'   => 'http://localhost'
            )
        );


        return $ret;
    }

}