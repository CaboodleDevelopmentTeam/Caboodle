<?php
/**
 * @copyright  2013 Enovation Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Grzegorz Adamowicz greg.adamowicz@enovation.ie
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');

// we reqire at least course id
//$courseid = required_param('course', PARAM_INT);
$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$myurl= new moodle_url('/blocks/caboodle/search.php');

$PAGE->set_url($myurl);
$PAGE->set_pagelayout('report');

require_login();

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('courses'), new moodle_url('/course/index.php'));
$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php?id='.$courseid));
$PAGE->navbar->add(get_string('caboodlesearch', 'block_caboodle'), $myurl);

echo $OUTPUT->header();

// search stuff from results

echo $OUTPUT->footer();
