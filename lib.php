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
 * Library of interface functions and constants for module groupdistribution
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the groupdistribution specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*////////////////////////////////////////////////////////////////////////////*/
// Moodle core API                                                            //
/*////////////////////////////////////////////////////////////////////////////*/

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function groupdistribution_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the groupdistribution into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $groupdistribution An object from the form in mod_form.php
 * @param mod_groupdistribution_mod_form $mform
 * @return int The id of the newly inserted groupdistribution record
 */
function groupdistribution_add_instance(stdClass $groupdistribution, mod_groupdistribution_mod_form $mform = null) {
    global $DB, $USER;

    $groupdistribution->timecreated = time();

    try {
        $transaction = $DB->start_delegated_transaction();
        if (property_exists($groupdistribution, 'data')) {
            foreach ($groupdistribution->data as $id => $data) {

                // Create a new entry in groupdistribution_data
                // so we need a groupsid but no id.
                $groupdata = new stdClass();
                $groupdata->groupsid   = $data['groupsid'];
                $groupdata->courseid   = $groupdistribution->courseid;
                $groupdata->maxsize    = $data['maxsize'];
                $groupdata->israteable = $data['rateable'];

                $DB->insert_record('groupdistribution_data', $groupdata);

                // Update the description of the group
                $groupdescription = new stdClass();
                $groupdescription->id          = $data['groupsid'];
                $groupdescription->description = $data['description']['text'];

                $DB->update_record('groups', $groupdescription);
            }
        }
        $id = $DB->insert_record('groupdistribution', $groupdistribution);
        // Add to course and groupdistribution log
        add_to_log($groupdistribution->courseid, 'course', 'add',
            'modedit.php?add=groupdistribution&course=' . $groupdistribution->courseid . '&section=0',
            'Created instance', $groupdistribution->coursemodule);
        add_to_log($groupdistribution->courseid, 'groupdistribution', 'add',
            'modedit.php?add=groupdistribution&course=' . $groupdistribution->courseid . '&section=0',
            'Created instance', $groupdistribution->coursemodule);

        $transaction->allow_commit();

        return $id;
    } catch (Exception $e) {
        $transaction->rollback($e);
    }
}

/**
 * Updates an instance of the groupdistribution in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $groupdistribution An object from the form in mod_form.php
 * @param mod_groupdistribution_mod_form $mform
 * @return boolean Success/Fail
 */
function groupdistribution_update_instance(stdClass $groupdistribution, mod_groupdistribution_mod_form $mform = null) {
    global $DB, $USER;

    $groupdistribution->timemodified = time();
    $groupdistribution->id = $groupdistribution->instance;

    try {
        $transaction = $DB->start_delegated_transaction();
        if (property_exists($groupdistribution, 'data')) {
            foreach ($groupdistribution->data as $id => $data) {
                $groupdata = new stdClass();
                $groupdata->maxsize    = $data['maxsize'];
                $groupdata->israteable = $data['rateable'];

                if ($DB->record_exists('groupdistribution_data', array('groupsid' => $data['groupsid']))) {

                    // groupdata already exists, use the id from the form to update it
                    $groupdata->id = $data['groupdataid'];
                    $DB->update_record('groupdistribution_data', $groupdata);
                } else {

                    // Create new entry in groupdata and set its groupsid
                    $groupdata->groupsid   = $data['groupsid'];
                    $groupdata->courseid   = $groupdistribution->courseid;
                    $DB->insert_record('groupdistribution_data', $groupdata);
                }

                // Update the description of the group
                $groupdescription = new stdClass();
                $groupdescription->id          = $data['groupsid'];
                $groupdescription->description = $data['description']['text'];

                $DB->update_record('groups', $groupdescription);
            }
        }
        // Update groupdistribution (including start/enddate)
        $bool = $DB->update_record('groupdistribution', $groupdistribution);

        // TODO: log what has been changed
        add_to_log($groupdistribution->courseid, 'groupdistribution', 'update',
            'modedit.php?update=' . $groupdistribution->coursemodule,
            'Saved changes', $groupdistribution->coursemodule);

        $transaction->allow_commit();

        return $bool;

    } catch (Exception $e) {
        $transaction->rollback($e);
    }
}

/**
 * Removes an instance of the groupdistribution from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function groupdistribution_delete_instance($id) {
    global $DB, $USER;

    $groupdistribution = $DB->get_record('groupdistribution', array('id' => $id));
    if (! $groupdistribution) {
        return false;
    }

    try {
        $transaction = $DB->start_delegated_transaction();

        $DB->delete_records('groupdistribution_ratings', array('courseid' => $groupdistribution->course));
        $DB->delete_records('groupdistribution_data', array('courseid' => $groupdistribution->course));
        $DB->delete_records('groupdistribution', array('id' => $id));

        add_to_log($groupdistribution->course, 'course', 'delete',
            'mod.php?delete=' . $groupdistribution->id,
            'Deleted groupdistribution', $groupdistribution->id);

        $transaction->allow_commit();
    } catch (Exception $e) {
        $transaction->rollback($e);
    }

    return true;
}

/**
 * Saves the ratings from user with $userid for the groups in the
 * course with $courseid.
 * $data should contain arrays with keys 'groupsid' and 'rating' which
 * specify the ratings for the respective groups.
 */
function save_ratings_to_db($courseid, $userid, array $data) {
    global $DB;

    try {
        $transaction = $DB->start_delegated_transaction();

        foreach ($data as $id => $rdata) {
            $rating = new stdClass();
            $rating->rating = $rdata['rating'];

            // Make sure that users can only change their own ratings

            // Test if the group belongs to the course
            $groupincourse = array('courseid' => $courseid, 'groupsid' => $rdata['groupsid']);
            if (! $DB->record_exists('groupdistribution_data', $groupincourse)) {
                print_error('group_not_in_course', 'groupdistribution');
            }

            $ratingexists = array('courseid' => $courseid, 'groupsid' => $rdata['groupsid'], 'userid' => $userid);
            if ($DB->record_exists('groupdistribution_ratings', $ratingexists)) {
                // The rating exists, we need to update its value
                // We get the id from the database to prevent users tampering with the html form

                $oldrating = $DB->get_record('groupdistribution_ratings', $ratingexists);
                $rating->id = $oldrating->id;
                $DB->update_record('groupdistribution_ratings', $rating);
            } else {
                // Create a new rating in the table

                $rating->userid = $userid;
                $rating->groupsid = $rdata['groupsid'];
                $rating->courseid = $courseid;
                $DB->insert_record('groupdistribution_ratings', $rating);
            }
        }
        $groupdistribution = $DB->get_record('groupdistribution', array('course' => $courseid));
        $coursemodule = get_coursemodule_from_instance('groupdistribution', $groupdistribution->id, $courseid, false, MUST_EXIST);

        add_to_log($courseid, 'groupdistribution', 'update',
            'view.php?id=' . $coursemodule->id,
            'User saved rating', $coursemodule->id);

        $transaction->allow_commit();
    } catch (Exception $e) {
        $transaction->rollback($e);
    }
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function groupdistribution_user_outline($course, $user, $mod, $groupdistribution) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $groupdistribution the module instance record
 * @return void, is supposed to echp directly
 */
function groupdistribution_user_complete($course, $user, $mod, $groupdistribution) {
}

function groupdistribution_get_logs($courseid, $timestart) {

    $selector = "l.course = :courseid";
    $selector .= " AND l.module = 'groupdistribution'";
    $selector .= " AND l.action = 'update'";
    $selector .= " AND l.url LIKE 'modedit%'";
    $selector .= " AND l.time > :timestart";
    $params = array('courseid' => $courseid, 'timestart' => $timestart);
    $count = 0;
    $logs = get_logs($selector, $params, 'l.time ASC', '', '', $count);
    return $logs;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in groupdistribution activities and print it out.
 * Return true if there was output, or false if there was none.
 *
 * @return boolean
 */
function groupdistribution_print_recent_activity($course, $viewfullnames, $timestart) {
    global $PAGE, $DB;

    $groupdistribution = $DB->get_record('groupdistribution', array('course' => $course->id));
    $renderer = $PAGE->get_renderer('mod_groupdistribution');

    $output = $renderer->format_notifications($groupdistribution, $timestart);

    if ($output !== '') {
        echo $output;
        return true;
    }
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link groupdistribution_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function groupdistribution_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    foreach (groupdistribution_get_logs($courseid, $timestart) as $log) {
        $act = new stdClass();
        $act->cmid = $cmid;
        $act->type = 'groupdistribution';
        $act->visible = true;
        $act->log = $log;

        $activities[$index++] = $act;
    }
}

/**
 * Prints single activity item prepared by {@see groupdistribution_get_recent_mod_activity()}
 *
 * @return void
 */
function groupdistribution_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    global $PAGE;

    $output = userdate($activity->log->time) . ':';
    $output .= '<br>';
    $output .= $activity->log->info;

    $renderer = $PAGE->get_renderer('mod_groupdistribution');
    echo $renderer->box($output);
}

function groupdistribution_print_overview($courses, &$htmlarray) {
    global $PAGE;
    $renderer = $PAGE->get_renderer('mod_groupdistribution');

    $groupdistributions = get_all_instances_in_courses('groupdistribution', $courses);

    if (!$groupdistributions) {
        return;
    }

    foreach ($groupdistributions as $gd) {
        $output = $renderer->format_notifications($gd,
            $courses[$gd->course]->lastaccess);

        if ($output !== '') {
            $htmlarray[$gd->course]['groupdistribution'] = $output;
        }
    }
}

/**
 * This activity does not use cron.
 *
 * @return boolean
 **/
function groupdistribution_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @return array
 */
function groupdistribution_get_extra_capabilities() {
    return array();
}
