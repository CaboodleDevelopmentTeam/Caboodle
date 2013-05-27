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
 * Strings for component 'caboodle', language 'en', branch 'MOODLE_24_STABLE'
 *
 * @package   caboodle
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Caboodle';
// Configuration section strings
$string['label_results'] = 'Amount of search results to save';
$string['desc_results'] = 'Search results being saved in block instance cache per resource';
$string['label_removecache'] = 'Clear cache after (seconds):';
$string['desc_removecache'] = 'This setting determines how often results cache will be purged and new search performed. Don\'t set it to less than 1h since it can cause high load on your server.
    Default value: 12h = 43200 seconds';
// End of configuration section strings

$string['caboodle:addinstance'] = 'Add a caboodle block';
$string['caboodle:myaddinstance'] = 'Add a caboodle block to my moodle';

$string['resources'] = 'Resources';
$string['no_resources'] = 'No resources configured';
$string['search'] = 'Search';
$string['caboodlesearch'] = 'Caboodle search';
$string['student_search'] = 'Student search enabled';
$string['student_search_help'] = 'If student search is enabled, students are allowed to perform search through added resources results';
$string['search_items_displayed'] = 'Number of search items displayed';
$string['search_items_displayed_help'] = 'Number of search items displayed in block';
$string['blacklist'] = 'Blacklist';
$string['blacklist_help'] = 'Click on "X" on the left of the black listed URL to remove it from list';
$string['search_results'] = 'Search results';
$string['advancedsearch'] = 'Advanced search';
$string['search_not_performed'] = 'Search not yet performed. It\'ll be updated soon.';
$string['yourownsearch'] = 'Conduct your own search:';
$string['search_on'] = '<h3>Search on "<i>{$a}</i>"</h3>';
$string['user_search_on'] = '<h3>User search on "<i>{$a}</i>"</h3>';
$string['nosearchstring'] = 'No search string provided!';
$string['nothing_found'] = 'Nothing found';
