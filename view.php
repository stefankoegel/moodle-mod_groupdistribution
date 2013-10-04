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
 * Prints a particular instance of groupdistribution
 *
 * @package    mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$courseid = optional_param('courseid', 0, PARAM_INT); // course ID
$action = optional_param('action', '', PARAM_TEXT);

if ($id) {
    $cm = get_coursemodule_from_id('groupdistribution', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $groupdistribution = $DB->get_record('groupdistribution', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($courseid) {
	$groupdistribution = $DB->get_record('groupdistribution', array('courseid' => $courseid), '*', MUST_EXIST);
	$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('groupdistribution', $groupdistribution->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'groupdistribution', 'view', "view.php?id={$cm->id}", $groupdistribution->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/groupdistribution/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($groupdistribution->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if($action == ACTION_RATE and is_enrolled($context)) {
	require_capability('mod/groupdistribution:give_rating', $context);

	// $data = required_param_array('data', PARAM_RAW, true);
	// required_param_array cannot handle arrays of arrays,
	// so this must be done by hand.
	$data = $_POST['data'];
	$data = clean_param_array($data, PARAM_INT, true);

	save_ratings_to_db($USER->id, $data);
	redirect($PAGE->url, get_string('ratings_saved', 'groupdistribution'));
}
if($action == ACTION_START) {
	require_capability('mod/groupdistribution:start_distribution', $context);
	test_shortest_path($COURSE->id);
}
if($action == ACTION_CLEAR) {
	require_capability('mod/groupdistribution:start_distribution', $context);
	clear_all_groups_in_course($COURSE->id);
}

// Output starts here
$renderer = $PAGE->get_renderer('mod_groupdistribution');
echo $renderer->header();

if($groupdistribution->intro) {
    echo $renderer->box(format_module_intro('groupdistribution', $groupdistribution, $cm->id),
    	'generalbox mod_introbox', 'groupdistributionintro');
}

if(has_capability('mod/groupdistribution:start_distribution', $context)) {
	echo $renderer->show_groupdistribution();
} else if(is_enrolled($context) and has_capability('mod/groupdistribution:give_rating', $context)) {
	echo $renderer->display_user_rating_form();
} else {
	echo $renderer->box(get_string('not_enrolled'));
}

// Finish the page
echo $renderer->footer();
