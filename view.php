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
 * @package    mod
 * @subpackage mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/view_form.php');

// Get the context, ids and action paramter
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$courseid = optional_param('courseid', 0, PARAM_INT); // course ID
$action = optional_param('action', '', PARAM_TEXT);

if ($id) {
    $cm = get_coursemodule_from_id('groupdistribution', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $groupdistribution = $DB->get_record('groupdistribution', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($courseid) {
    $groupdistribution = $DB->get_record('groupdistribution', array('course' => $courseid), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('groupdistribution', $groupdistribution->id, $course->id, false, MUST_EXIST);
} else {
    print_error('unspecifycourseid');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

add_to_log($course->id, 'groupdistribution', 'view', "view.php?id={$cm->id}", $groupdistribution->name, $cm->id);


// Print the page header
$PAGE->set_url('/mod/groupdistribution/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($groupdistribution->name));
$PAGE->set_heading(format_string($groupdistribution->name));
$PAGE->set_context($context);


// Process form: Start distribution and redirect after finishing
if (has_capability('mod/groupdistribution:start_distribution', $context)) {
    // Start the distribution algorithm
    if ($action == ACTION_START) {
        require_capability('mod/groupdistribution:start_distribution', $context);

        distribute_users_in_course($COURSE->id);

        redirect($PAGE->url->out(), get_string('distribution_saved', 'groupdistribution'));
    }
}

// Save the user's rating
if (has_capability('mod/groupdistribution:give_rating', $context)) {
    // Save the user's rating
    $mform = new mod_groupdistribution_view_form($PAGE->url->out());

    if ($mform->is_validated() and !$mform->is_cancelled() and $data = $mform->get_data()) {
        if ($action == ACTION_RATE) {
            require_capability('mod/groupdistribution:give_rating', $context);

            save_ratings_to_db($COURSE->id, $USER->id, $data->data);

            redirect($PAGE->url->out(), get_string('ratings_saved', 'groupdistribution'));
        }
    }
}


// Output starts here
$renderer = $PAGE->get_renderer('mod_groupdistribution');
echo $renderer->header();

// Print header, intro and start/end information
echo $renderer->format_groupdistribution($groupdistribution);

// Get current time
$now = time();

// Print data and controls for students
if (has_capability('mod/groupdistribution:give_rating', $context)) {
    if ($groupdistribution->begindate > $now) {
        echo $renderer->user_rating_form_tooearly();
    } else if ($groupdistribution->enddate < $now) {
        echo $renderer->user_rating_form_finished();
    } else {
        echo $renderer->user_rating_form_ready($mform);
    }
}

// Print data and controls for teachers
if (has_capability('mod/groupdistribution:start_distribution', $context)) {
    // Notify if there aren't at least two rateable groups
    if (count(get_rateable_groups_for_course($COURSE->id)) < 2) {
        echo $renderer->notification(get_string('at_least_two_rateable_groups', 'groupdistribution'));
    }

    // Print group distribution algorithm control
    if ($groupdistribution->enddate < $now) {
        echo $renderer->groupdistribution_algorithm_control_ready();
    } else {
        echo $renderer->groupdistribution_algorithm_control_tooearly();
    }

    // Print distribution table
    if ($groupdistribution->enddate < $now) {
        echo $renderer->distribution_table_for_course($COURSE->id);
    }

    // Print ratings table
    if ($action == SHOW_TABLE) {
        echo $renderer->ratings_table_for_course($COURSE->id);
    } else {
        echo $renderer->show_ratings_table_button();
    }
}

// Finish the page
echo $renderer->footer();
