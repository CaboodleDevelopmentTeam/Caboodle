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
 * caboodle block caps.
 *
 */

defined('MOODLE_INTERNAL') || die();

// get local lib
require_once($CFG->dirroot . '/blocks/caboodle/lib.php');


class block_caboodle extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_caboodle');
    }

    function get_content() {

        global $CFG, $OUTPUT;

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

        $this->content = '';
        if (empty($currentcontext)) {
            return $this->content;
        }
        if ($this->page->course->id == SITEID) {
            $this->context->text .= "site context";
        }

        if (! empty($this->config->text)) {
            $this->content->text .= $this->config->text;
        }

        $this->content->text .= "<div>";

        if ($this->config->student_search) {
            $this->content->text .= $this->get_search_form();
        }

        echo "<pre>"; var_dump($this->config); echo "</pre>";

        $search_str = $this->config->search;

        if (!empty($search_str)) {

            // get all resources
            $resources = caboodle::get_resources();

            $this->content->text .= '<h3>Search on "<i>' . $search_str . '</i>"</h3>';

            foreach ($resources as $resid => $resource) {
                if ($this->config->resource[$resid] == 1) {

                    $this->content->text .= "<h4>" . $resource->name . "</h4>";

                    $results = caboodle::get_results($resid);

                    $this->content->text .= "<ul>";

                    if (!empty($results)) {

                        foreach($results as $r => $result) {
                            $this->content->text .= "<li>";

                            $this->content->text .= "Result";

                            $this->content->text .= "</li>";
                        }

                    } else {
                        // no results
                        $this->content->text .=  '<li>'. get_string('nothing_found', 'block_caboodle') . '</li>';
                    }

                    $this->content->text .= "</ul>";

                } // if
            } // foreach

        } else {
            // no search string
            $this->content->text .= '<h3>'. get_string('nosearchstring', 'block_caboodle') . '</h3>';
        }

        $this->content->text .= "</div>";
        return $this->content;
    }

    public function get_search_form() {
        global $CFG, $OUTPUT;

        $strsearch  = get_string('search');
        $strgo      = get_string('go');
        $advancedsearch = get_string('advancedsearch', 'block_caboodle');
        $yourownsearch = get_string('yourownsearch', 'block_caboodle');


        $text  = '<div class="searchform">';
        $text .= '<p>' . $yourownsearch . '</p>';
        $text .= '<form action="?" style="display:inline"><fieldset class="invisiblefieldset">';
        $text .= '<legend class="accesshide">'.$strsearch.'</legend>';
        $text .= '<input name="id" type="hidden" value="'.$this->page->course->id.'" />';  // course
        $text .= '<label class="accesshide" for="searchform_search">'.$strsearch.'</label>'.
                 '<input id="searchform_search" name="caboodlesearch" type="text" size="12" />';
        $text .= '<button id="searchform_button" type="submit" title="'.$strsearch.'">'.$strgo.'</button><br />';
        // advanced search
//        $text .= '<a href="'.$CFG->wwwroot.'/blocks/caboodle/search.php?id='.$this->page->course->id.'">'.$advancedsearch.'</a>';
//        $text .= $OUTPUT->help_icon('search');
        $text .= '</fieldset></form></div>';


        return $text;
    }

    public function applicable_formats() {
        return array('all' => false,
                     'course-view' => true
                    );
    }

    public function instance_allow_multiple() {
          return false;
    }

    function has_config() { return true; }

    public function cron() {
            mtrace("block_caboodle cron");

                 // do something

        return true;
    }
}
