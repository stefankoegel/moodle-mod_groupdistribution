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
 * @package    mod
 * @subpackage mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/view_form.php');

class mod_groupdistribution_renderer extends plugin_renderer_base {

    /**
     * Formats the $groupdistribution (name, desription, begin and enddate) and returns HTML.
     */
    public function format_groupdistribution($groupdistribution) {
        global $COURSE;

        $output = $this->heading(format_string($groupdistribution->name), 2);

        if ($groupdistribution->intro) {
            $cm = get_coursemodule_from_instance('groupdistribution', $groupdistribution->id, $COURSE->id, false, MUST_EXIST);
            $output .= $this->box(format_module_intro('groupdistribution', $groupdistribution, $cm->id), 'generalbox', 'intro');
        }

        $output .= $this->box_start();

        $a = new stdClass();
        $begin = userdate($groupdistribution->begindate);
        $a->begin = '<span class="groupdistribution_highlight">'.$begin.'</span>';
        $end = userdate($groupdistribution->enddate);
        $a->end = '<span class="groupdistribution_highlight">'.$end.'</span>';
        $note = get_string('show_rating_period', 'groupdistribution', $a);
        $output .= '<p>'.$note.'</p>';

        $output .= $this->box_end();

        return $output;
    }

    /**
     * Output the rating form section (as long as the rating period has not yet started)
     */
    public function user_rating_form_tooearly() {
        global $COURSE;

        $output = $this->heading(get_string('your_rating', 'groupdistribution'), 2);

        $output .= $this->notification(get_string('too_early_to_rate', 'groupdistribution'));

        $groups = get_rateable_groups_for_course($COURSE->id);

        foreach ($groups as $group) {
            $output .= $this->format_group($group);
        }

        return $output;
    }

    /**
     * Output the rating form section (as long as the rating period is running)
     */
    public function user_rating_form_ready(mod_groupdistribution_view_form $mform) {
        $output = $this->heading(get_string('your_rating', 'groupdistribution'), 2);

        $output .= $mform->to_html();

        return $output;
    }

    /**
     * Output the rating form section (as long as the rating perios has already finished)
     */
    public function user_rating_form_finished() {
        global $COURSE, $USER;

        $output = $this->heading(get_string('your_rating', 'groupdistribution'), 2);

        $output .= $this->notification(get_string('rating_is_over', 'groupdistribution'));

        $memberships = get_rateable_memberships_for_course_with_user($COURSE->id, $USER->id);
        if (count($memberships) > 0) {
            $output .= $this->heading('Deine Gruppen', 2);
            foreach ($memberships as $mem) {
                $output .= $this->format_group($mem);
            }
        }

        return $output;
    }

    /**
     * Output the groupdistribution algorithm control section (as long as the rating period is not over)
     */
    public function groupdistribution_algorithm_control_tooearly() {
        $output = $this->heading(get_string('distribution_algorithm', 'groupdistribution'), 2);

        // Rating period is not over, tell the teacher
        $note = get_string('too_early_to_distribute', 'groupdistribution');
        $output .= $this->notification($note);

        return $output;
    }

    /**
     * Output the groupdistribution algorithm control section (as soon as the rating period is over)
     */
    public function groupdistribution_algorithm_control_ready() {
        global $PAGE, $COURSE, $DB;

        $starturl = new moodle_url($PAGE->url, array('action' => ACTION_START));

        $groupdistribution = $DB->get_record('groupdistribution', array('course' => $COURSE->id));

        $output = $this->heading(get_string('distribution_algorithm', 'groupdistribution'), 2);

        // Rating period is over, show the button
        $output .= $this->box_start();
        $output .= '<p>'.get_string('start_distribution_explanation', 'groupdistribution').'</p>';
        $output .= $this->box_end();
        $output .= $this->single_button($starturl->out(), get_string('start_distribution', 'groupdistribution'), 'get');

        return $output;
    }

    /**
     * Shows table containing information about the result of the distribution algorithm.
     *
     * @return HTML code
     */
    public function distribution_table_for_course($courseid) {
        // Count the number of distributions with a specific rating
        $distributiondata = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
        $memberships = memberships_per_course($courseid);
        foreach ($memberships as $userid => $groups) {
            $best = 0;
            // If a user is in multiple rateable groups, only count
            // the one with the best rating.
            foreach ($groups as $groupsid => $rating) {
                if ($rating > $best and 1 <= $rating and $rating <= 5) {
                    $best = $rating;
                }
            }
            if (1 <= $best and $best <= 5) {
                $distributiondata[$best]++;
            }
        }

        $distributionrow = array();
        $distributionhead = array();
        foreach ($distributiondata as $rating => $count) {
            $cell = new html_table_cell();
            $cell->text = $count;
            $cell->attributes['class'] = 'groupdistribution_rating_' . $rating;
            $distributionrow[$rating] = $cell;

            $cell = new html_table_cell();
            $cell->text = get_string('rating_short_' . $rating, 'groupdistribution');
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

        $output = $this->heading(get_string('distribution_table', 'groupdistribution'), 2);
        $output .= $this->box_start();
        $output .= '<p>'.get_string('view_distribution_table', 'groupdistribution').'</p>';
        $output .= html_writer::table($distributiontable);
        $output .= $this->box_end();

        return $output;
    }

    /**
     * Shows table containing information about the users' ratings
     * and their distribution over the groups (group memberships).
     *
     * @return HTML code
     */
    public function ratings_table_for_course($courseid) {
        $config_show_names = get_config('mod_groupdistribution', 'show_names');

        $groups = get_rateable_groups_for_course($courseid);
        $groupnames = array();
        foreach ($groups as $group) {
            $groupnames[$group->id] = $group->name;
        }

        $ratings = all_ratings_for_rateable_groups_from_raters_in_course($courseid);
        $ratingscells = array();
        foreach ($ratings as $rating) {

            // Create a cell in the table for each rating
            if (!array_key_exists($rating->userid, $ratingscells)) {
                $ratingscells[$rating->userid] = array();
            }
            $cell = new html_table_cell();
            $cell->text = get_string('rating_short_' . $rating->rating, 'groupdistribution');
            $cell->attributes['class'] = 'groupdistribution_rating_' . $rating->rating;

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
            if ($config_show_names) {
                // -1 is smaller than any id
                $ratingscells[$user->id][-1] = self::format_user_data($user);
            }
            // Sort ratings by groupid to align them with the group names in the table
            ksort($ratingscells[$user->id]);
        }

        if ($config_show_names) {
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

        $output = $this->heading(get_string('ratings_table', 'groupdistribution'), 2);
        $output .= $this->box_start();
        $output .= '<p>'.get_string('view_ratings_table_explanation', 'groupdistribution').'</p>';
        $output .= $this->box(html_writer::table($ratingstable), 'groupdistribution_ratings_box');
        $output .= $this->box_end();

        return $output;
    }

    /**
     * Renders the button to show the ratings table
     */
    public function show_ratings_table_button() {
        global $PAGE;

        $tableurl = new moodle_url($PAGE->url, array('action' => SHOW_TABLE));

        $output = $this->heading(get_string('ratings_table', 'groupdistribution'), 2);
        $output .= $this->box_start();
        $output .= get_string('view_ratings_table', 'groupdistribution');
        // Button to display information about the distribution and ratings
        $output .= $this->single_button($tableurl->out(),
                get_string('show_table', 'groupdistribution'), 'get');
        $output .= $this->box_end();

        return $output;
    }

    /**
     * Formats the names and pictures of $teachers and returns HTML.
     */
    public function format_group_teachers($teachers) {
        global $COURSE, $PAGE, $CFG, $USER;

        $output = $this->heading(get_string('group_teachers', 'groupdistribution'), 5, 'groupdistribution_heading');

        $output .= $this->box_start('groupdistribution_teachers clearfix');
        $output .= html_writer::start_tag('ul');

        foreach ($teachers as $teacher) {
            $output .= html_writer::start_tag('li');

            $output .= $this->user_picture($teacher, array('size' => 60, 'link' => true, 'courseid' => $COURSE->id));
            $output .= html_writer::start_tag('div', array('class' => 'name'));
            $output .= fullname($teacher);
            $output .= html_writer::end_tag('div');

            $output .= html_writer::end_tag('li');
        }

        $output .= html_writer::end_tag('ul');
        $output .= $this->box_end();

        return $output;
    }

    /**
     * Formats the picture of $group and return HTML.
     */
    public function format_group_picture($group) {
        global $COURSE;

        $picture = print_group_picture($group, $COURSE->id, true, true, false);
        $output = $this->box_start('groupdistribution_grouppicture');
        $output .= $picture;
        $output .= $this->box_end();

        return $output;
    }

    /**
     * Formats the $description and return HTML.
     */
    public function format_group_description($description) {
        $output = $this->box_start('groupdistribution_description clearfix');
        $output .= $this->heading(get_string('group_description', 'groupdistribution'), 5, 'groupdistribution_heading');
        $output .= format_text($description);
        $output .= $this->box_end();

        return $output;
    }

    /**
     * Formats a group for display to the students
     */
    public function format_group($group) {
        $output = $this->box_start('generalbox');

        $output .= $this->heading($group->name, 3, 'groupdistribution_heading');

        if ($group->picture == 1 and $group->hidepicture != 1) {
            $output .= $this->format_group_picture($group);
        }

        if ($group->description !== '') {
            $output .= $this->format_group_description($group->description);
        }

        $teachers = every_group_teacher_in_group($group->courseid, $group->id);
        if (count($teachers) > 0) {
            $output .= '<hr />';
            $output .= $this->format_group_teachers($teachers);
        } 
        $output .= $this->box_end();

        return $output;
    }

    /**
     * Format the users in the rating table
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

    /**
     * Formats the notifications for the recent activity block and the course overview block
     */
    public function format_notifications($groupdistribution, $timestart) {
        $output = '';

        if ($groupdistribution->begindate < time() and time() < $groupdistribution->enddate) {
            // during the rating period.
            $a = new stdclass();
            $a->until = userdate($groupdistribution->enddate);
            $output .= $this->container(get_string('rating_has_begun', 'groupdistribution', $a), 'overview groupdistribution');
        }

        $logs = groupdistribution_get_logs($groupdistribution->course, $timestart);
        if (count($logs) > 0) {
            $a = new stdclass();

            $changes = array();
            foreach ($logs as $log) {
                $changes = array_merge($log->expandedinfo, $changes);
            }

            $a->changes = '<br><ul><li>' . implode('</li><li>', array_keys($changes)). '</li></ul>';
            $a->time = userdate($timestart);
            $output .= $this->container(get_string('changes', 'groupdistribution', $a), 'overview groupdistribution');
        }

        return $output;
    }
}
