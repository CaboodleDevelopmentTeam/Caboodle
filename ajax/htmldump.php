<?php
/**
 * @copyright  2013 Enovation Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Grzegorz Adamowicz greg.adamowicz@enovation.ie
 */
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot .'/blocks/caboodle/lib.php');

if (!$courseid = optional_param('courseid', false, PARAM_INT)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "\nCourse ID missing!\n";
  //  die();
}

if (!$htmltoadd = optional_param('html_list', false, PARAM_RAW)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "\nData to add missing!\n";
    die();
}

// decode html
$htmltoadd = urldecode(base64_decode(urldecode($htmltoadd)));

$myurl= new moodle_url('/blocks/caboodle/ajax/htmldump.php', array('course' => $courseid));

$PAGE->set_url($myurl);

$course = $DB->get_record('course',array('id' => $courseid));

if (!$course) {
    print_error('invalidcourseid');
}

// get course context
$context = context_course::instance($course->id);

// user need to be able to update this course
if (!has_capability('moodle/course:update', $context)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "\n\nYou don't have permission to do that!\n";
    die();
}

// double check - is logged in? (this will redirect to login page if not)
require_login();
// has capability?
require_capability('moodle/course:update', $context);

// set page context
$PAGE->set_context($context);

// proceed with adding label to a course
$htmldump = new caboodle_htmldump($courseid, $htmltoadd);

try {
    $htmldump->insert_new_label();
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "\nOperation failed!\n";
    echo "Last error: " . $e->getMessage();
    die();
}

header("HTTP/1.1 200 OK");
echo "OK";