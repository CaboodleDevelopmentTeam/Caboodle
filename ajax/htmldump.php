<?php
/**
 * @copyright  2013 Enovation Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Grzegorz Adamowicz greg.adamowicz@enovation.ie
 */
require_once(dirname(__FILE__) . '/../../../config.php');
require_once(dirname(__FILE__) .'/../lib.php');

$courseid = required_param('courseid', PARAM_INT);
$htmltoadd = required_param('html_list', PARAM_RAW);
// decode html
$htmltoadd = base64_decode(urldecode($htmltoadd));

$myurl= new moodle_url('/report/cpd/index.php', array('course' => $courseid));

$PAGE->set_url($myurl);
//$PAGE->set_pagelayout('report');

require_login();

$course = $DB->get_record('course',array('id' => $courseid));

if (!$course) {
    print_error('invalidcourseid');
}

// get course context
$context = context_course::instance($course->id);

// user need to be able to update this course
require_capability('moodle/course:update', $context);

$PAGE->set_context($context);

// proceed with adding label to a course