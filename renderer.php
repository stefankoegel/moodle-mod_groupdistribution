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
 * @package    mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');
require_once('view_form.php');

class mod_groupdistribution_renderer extends plugin_renderer_base {

    /**
     * Returns HTML code if the user can not yet give ratings or is too late
     * to give a rating.
     * If the user is on time for the rating it returns the forms HTML in which
     * the user can enter his ratings.
     * 
     * @param $mform view_form to show if the user is on time to give ratings
     * @return HTML code
     */
    public function user_rating_form(mod_groupdistribution_view_form $mform) {
        global $DB, $COURSE;

        $groupdistribution = $DB->get_record('groupdistribution', array('course' => $COURSE->id));

        $output = '';
        $output .= self::show_rating_period($groupdistribution);
        
        $a = new stdClass();
        $a->begin = userdate($groupdistribution->begindate);
        $a->end = userdate($groupdistribution->enddate);

        if (time() < $groupdistribution->begindate) {
            $output .= $this->notification(get_string('too_early_to_rate', 'groupdistribution', $a));
        } else if ($groupdistribution->enddate < time()) {
            $output .= $this->notification(get_string('rating_is_over', 'groupdistribution'));
        } else {
            $output .= $mform->to_html();
        }

        return $output;
    }

    private function show_rating_period($groupdistribution) {

        $a = new stdClass();
        $a->begin = userdate($groupdistribution->begindate);
        $a->end = userdate($groupdistribution->enddate);
        $note = get_string('show_rating_period', 'groupdistribution', $a);

        return $this->notification($note, 'notifysucces');
    }

    public function start_distribution_button() {
        global $PAGE, $COURSE, $DB;

        $starturl = new moodle_url($PAGE->url, array('action' => ACTION_START));

        $groupdistribution = $DB->get_record('groupdistribution', array('course' => $COURSE->id));

        $output = '';
        $output .= self::show_rating_period($groupdistribution);

        if ($groupdistribution->enddate < time()) {

            // Rating period is over, show the button
            $output .= $this->box_start();
            $output .= get_string('start_distribution_explanation', 'groupdistribution');
            $output .= '<br><br>';
            $output .= $this->single_button($starturl->out(),
                    get_string('start_distribution', 'groupdistribution'), 'get');
            $output .= $this->box_end();
        } else {

            // Rating period is not over, tell the teacher
            $a = new stdClass();
            $a->begin = userdate($groupdistribution->begindate);
            $a->end = userdate($groupdistribution->enddate);
            $note = get_string('too_early_to_distribute', 'groupdistribution', $a);
            $output .= $this->notification($note);
        }
        return $output;
    }

    public function show_table_button() {
        global $PAGE;

        $tableurl = new moodle_url($PAGE->url, array('action' => SHOW_TABLE));

        $output = '';
        $output .= $this->box_start();
        $output .= get_string('view_distribution_table', 'groupdistribution');
        $output .= '<br><br>';
        // Button to display information about the distribution and ratings
        $output .= $this->single_button($tableurl->out(),
                get_string('show_table', 'groupdistribution'), 'get');
        $output .= $this->box_end();

        return $output;
    }

    /**
     * Shows tables containing information about the users' ratings
     * and their distribution over the groups (group memberships).
     *
     * @return HTML code
     */
    public function ratings_table_for_course($courseid) {
        global $CFG;

        $groups = get_rateable_groups_for_course($courseid);
        $groupnames = array();
        foreach ($groups as $group) {
            $groupnames[$group->id] = $group->name;
        }

        $ratings = all_ratings_for_rateable_groups_from_raters_in_course($courseid);
        $ratingscells = array();
        $ratingnames = get_rating_names();
        foreach ($ratings as $rating) {

            // Create a cell in the table for each rating
            if (!array_key_exists($rating->userid, $ratingscells)) {
                $ratingscells[$rating->userid] = array();
            }
            $cell = new html_table_cell();
            $cell->text = get_string('rating_' . $ratingnames[$rating->rating], 'groupdistribution');
            $cell->attributes['class'] = 'groupdistribution_rating_' . $ratingnames[$rating->rating];

            $ratingscells[$rating->userid][$rating->groupsid] = $cell;
        }

        // If there is no rating from a user for a group,
        // put a 'no_rating_given' cell into the table.
        $usersincourse = every_rater_in_course($courseid);
        foreach ($usersincourse as $user) {
            if (!array_key_exists($user->id, $ratingscells)) {
                $ratingscells[$user->id] = array();
            }
            foreach ($groupnames as $groupsid => $name) {
                if (!array_key_exists($groupsid, $ratingscells[$user->id])) {
                    $cell = new html_table_cell();
                    $cell->text = get_string('no_rating_given', 'groupdistribution');
                    $cell->attributes['class'] = 'groupdistribution_rating_none';
                    $ratingscells[$user->id][$groupsid] = $cell;
                }
            }
            if ($CFG->groupdistribution_show_names) {
                // -1 is smaller than any id
                $ratingscells[$user->id][-1] = self::format_user_data($user);
            }
            // Sort ratings by groupid to align them with the group names in the table
            ksort($ratingscells[$user->id]);
        }

        if ($CFG->groupdistribution_show_names) {
            // -1 is smaller than any id
            $groupnames[-1] = 'User';
        }
        // Sort group names by groupid
        ksort($groupnames);

        // Highlight ratings according to which users have been distributed
        // and count the number of such distributions
        $memberships = memberships_per_course($courseid);
        foreach ($memberships as $userid => $groups) {
            foreach ($groups as $groupsid => $rating) {
                if (array_key_exists($userid, $ratingscells)
                  and array_key_exists($groupsid, $ratingscells[$userid])) {

                    // Highlight the cell
                    $ratingscells[$userid][$groupsid]->attributes['class'] .= ' groupdistribution_member';
                }
            }
        }

        // The ratings table shows the users' ratings for the groups
        $ratingstable = new html_table();
        $ratingstable->data = $ratingscells;
        $ratingstable->head = $groupnames;
        $ratingstable->attributes['class'] = 'groupdistribution_ratings_table';

        $output = '';
        $output .= $this->box_start();
        $output .= get_string('ratings_table', 'groupdistribution');
        $output .= '<br><br>';
        $output .= $this->box(html_writer::table($ratingstable), 'groupdistribution_ratings_box');
        $output .= $this->box_end();

        return $output;
    }

    /**
     * Formats the names and pictures of $teachers and returns HTML.
     */
    public function format_group_teachers($teachers) {
        global $COURSE;

        $output = '';
        $output .= $this->box_start();
        foreach ($teachers as $teacher) {
            $output .= $this->box_start('groupdistribution_user');
            $output .= $this->user_picture($teacher, array('courseid' => $COURSE->id));
            $output .= fullname($teacher);
            $output .= $this->box_end();
        }
        $output .= $this->box_end();
        $output .= '<br>';

        return $output;
    }

    /**
     * Formats the picture of $group and return HTML.
     */
    public function format_group_picture($group) {
        global $COURSE;

        $picture = print_group_picture($group, $COURSE->id, false, true);
        $output = $this->box_start();
        $output .= $this->heading(get_string('group_picture', 'groupdistribution'), 5, 'groupdistribution_heading'); 
        $output .= $picture;
        $output .= $this->box_end();
        $output .= '<br>';

        return $output;
    }

    /**
     * Taken with permission from block_people:
     *   https://github.com/moodleuulm/moodle-block_people
     */
    public function format_user_data($data) {
        global $CFG, $OUTPUT, $USER, $COURSE, $PAGE;

        $output = '';
        $output .= html_writer::start_tag('div', array('class' => 'groupdistribution_user'));
        $output .= html_writer::start_tag('div', array('class' => 'name'));
        $output .= fullname($data);
        $output .= html_writer::end_tag('div');
        $output .= html_writer::start_tag('div', array('class' => 'icons'));
        if (has_capability('moodle/user:viewdetails', $PAGE->context)) {
            $a = array();
            $a['href'] = new moodle_url('/user/view.php', array('id' => $data->id, 'course' => $COURSE->id));
            $a['title'] = get_string('viewprofile', 'core');
            $output .= html_writer::start_tag('a', $a);

            $src = array('src' => $OUTPUT->pix_url('i/user'), 'class' => 'icon', 'alt' => get_string('viewprofile', 'core'));
            $output .= html_writer::empty_tag('img', $src);

            $output .= html_writer::end_tag('a');
        }

        if ($CFG->messaging && has_capability('moodle/site:sendmessage', $PAGE->context) && $data->id != $USER->id) {
            $a = array();
            $a['href'] = new moodle_url('/message/index.php', array('id' => $data->id));
            $a['title'] = get_string('sendmessageto', 'core_message', fullname($data));
            $output .= html_writer::start_tag('a', $a);

            $src = array('src' => $OUTPUT->pix_url('t/email'), 'class' => 'icon');
            $src['alt'] = get_string('sendmessageto', 'core_message', fullname($data));
            $output .= html_writer::empty_tag('img', $src);

            $output .= html_writer::end_tag('a');
        }
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        return $output;
    }

    public function distribution_table_for_course($courseid) {

        // Count the number of distributions with a specific rating
        $distributiondata = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
        $memberships = memberships_per_course($courseid);
        foreach ($memberships as $userid => $groups) {
            foreach ($groups as $groupsid => $rating) {
                if (1 <= $rating and $rating <= 5) {
                    // Increment the counter for users with this rating
                    $distributiondata[$rating]++;
                }
            }
        }

        $distributionrow = array();
        $distributionhead = array();
        $ratingnames = get_rating_names();
        foreach ($distributiondata as $rating => $count) {
            $cell = new html_table_cell();
            $cell->text = $count;
            $cell->attributes['class'] = 'groupdistribution_rating_' . $ratingnames[$rating];
            $distributionrow[$rating] = $cell;

            $cell = new html_table_cell();
            $cell->text = get_string('rating_' . $ratingnames[$rating], 'groupdistribution');
            $distributionhead[$rating] = $cell;
        }

        $cell = new html_table_cell();
        $usersincourse = every_rater_in_course($courseid);
        $cell->text = count($usersincourse) - count($memberships);
        $distributionrow[] = $cell;

        $cell = new html_table_cell();
        $cell->text = get_string('unassigned_users', 'groupdistribution');
        $distributionhead[] = $cell;

        // The distribution table shows how many users got into a group with a
        // good/ok/bad... rating
        $distributiontable = new html_table();
        $distributiontable->data = array($distributionrow);
        $distributiontable->head = $distributionhead;

        $output = '';
        $output .= $this->box_start();
        $output .= get_string('distribution_table', 'groupdistribution');
        $output .= '<br><br>';
        $output .= html_writer::table($distributiontable);
        $output .= $this->box_end();

        return $output;
    }
}
