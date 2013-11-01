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
	function user_rating_form(mod_groupdistribution_view_form $mform) {
		global $DB, $COURSE;

		$groupdistribution = $DB->get_record('groupdistribution', array('courseid' => $COURSE->id));

		$output = '';
		$output .= mod_groupdistribution_renderer::show_rating_period($groupdistribution);

		if(time() < $groupdistribution->begindate) {
			$output .= $this->notification(get_string('too_early_to_rate', 'groupdistribution', $a));
		} else if($groupdistribution->enddate < time()) {
			$output .= $this->notification(get_string('rating_is_over', 'groupdistribution'));
		} else {
			$output .= $mform->toHtml();
		}

		return $output;
	}

	function show_rating_period($groupdistribution) {

		$a = new stdClass();
		$a->begin = userdate($groupdistribution->begindate);
		$a->end = userdate($groupdistribution->enddate);
		$note = get_string('show_rating_period', 'groupdistribution', $a);

		return $this->notification($note, 'notifysucces');
	}

	function start_distribution_button() {
		global $PAGE, $COURSE, $DB;

		$startURL = new moodle_url($PAGE->url, array('action' => ACTION_START));

		$groupdistribution = $DB->get_record('groupdistribution', array('courseid' => $COURSE->id));

		$output = '';
		$output .= mod_groupdistribution_renderer::show_rating_period($groupdistribution);

		if($groupdistribution->enddate < time()) {

			// Rating period is over, show the button
			$output .= $this->box_start();
			$output .= get_string('start_distribution_explanation', 'groupdistribution');
			$output .= '<br><br>';
			$output .= $this->single_button($startURL->out(),
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

	function show_table_button() {
		global $PAGE;

		$tableURL = new moodle_url($PAGE->url, array('action' => SHOW_TABLE));

		$output = '';
		$output .= $this->box_start();
		$output .= get_string('view_distribution_table', 'groupdistribution');
		$output .= '<br><br>';
		// Button to display information about the distribution and ratings
		$output .= $this->single_button($tableURL->out(),
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
	function ratings_table_for_course($courseid) {
		global $CFG;

		$groups = get_rateable_groups_for_course($courseid);
		$groupNames = array();
		foreach($groups as $group) {
			$groupNames[$group->id] = $group->name;
		}

		$ratings = get_all_ratings_for_rateable_groups_in_course($courseid);
		$ratings_cells = array();
		$rating_names = get_rating_names();
		foreach($ratings as $rating) {

			// Create a cell in the table for each rating
			if(!array_key_exists($rating->userid, $ratings_cells)) {
				$ratings_cells[$rating->userid] = array();
			}
			$cell = new html_table_cell();
			$cell->text = get_string('rating_' . $rating_names[$rating->rating], 'groupdistribution');
			$cell->attributes['class'] = 'groupdistribution_rating_' . $rating_names[$rating->rating];

			$ratings_cells[$rating->userid][$rating->groupsid] = $cell;
		}

		// If there is no rating from a user for a group,
		// put a 'no_rating_given' cell into the table.
		$users_in_course = all_enrolled_users_in_course($courseid);
		foreach($users_in_course as $user) {
			if(!array_key_exists($user->id, $ratings_cells)) {
				$ratings_cells[$user->id] = array();
			}
			foreach($groupNames as $groupsid => $name) {
				if(!array_key_exists($groupsid, $ratings_cells[$user->id])) {
					$cell = new html_table_cell();
					$cell->text = get_string('no_rating_given', 'groupdistribution');
					$ratings_cells[$user->id][$groupsid] = $cell;
				}
			}
			if($CFG->groupdistribution_show_names) {
				// -1 is smaller than any id
				$ratings_cells[$user->id][-1] = mod_groupdistribution_renderer::format_user_data($user);
			}
			// Sort ratings by groupid to align them with the group names in the table
			ksort($ratings_cells[$user->id]);
		}

		if($CFG->groupdistribution_show_names) {
			// -1 is smaller than any id
			$groupNames[-1] = 'User';
		}
		// Sort group names by groupid
		ksort($groupNames);

		// Highlight ratings according to which users have been distributed
		// and count the number of such distributions
		$memberships = memberships_per_course($courseid);
		foreach($memberships as $userid => $groups) {
			foreach($groups as $groupsid => $rating) {
				if(array_key_exists($userid, $ratings_cells)
				  and array_key_exists($groupsid, $ratings_cells[$userid])) {

					// Highlight the cell
					$ratings_cells[$userid][$groupsid]->attributes['class'] .= ' groupdistribution_member';
				}
			}
		}

		// The ratings table shows the users' ratings for the groups
		$ratings_table = new html_table();
		$ratings_table->data = $ratings_cells;
		$ratings_table->head = $groupNames;

		$output = '';
		$output .= $this->box_start();
		$output .= get_string('ratings_table', 'groupdistribution');
		$output .= '<br><br>';
		$output .= $this->box(html_writer::table($ratings_table), 'groupdistribution_ratings_box');
		$output .= $this->box_end();

		return $output;
	}

	/**
	 * Taken with permission from block_people:
	 *   https://github.com/moodleuulm/moodle-block_people
	 */
	function format_user_data($data) {
		global $CFG, $OUTPUT, $USER, $COURSE, $PAGE;

		$output = '';
		$output .= html_writer::start_tag('div', array('class' => 'groupdistribution_user'));
		$output .= html_writer::start_tag('div', array('class' => 'name'));
		$output .= fullname($data);
		$output .= html_writer::end_tag('div');
		$output .= html_writer::start_tag('div', array('class' => 'icons'));
		if (has_capability('moodle/user:viewdetails', $PAGE->context)) {
			$output .= html_writer::start_tag('a', array('href' => new moodle_url('/user/view.php', array('id' => $data->id, 'course' => $COURSE->id)), 'title' => get_string('viewprofile', 'core')));
			$output .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/user'), 'class' => 'icon', 'alt' => get_string('viewprofile', 'core')));
			$output .= html_writer::end_tag('a');
		}

		if ($CFG->messaging && has_capability('moodle/site:sendmessage', $PAGE->context) && $data->id != $USER->id) {
			$output .= html_writer::start_tag('a', array('href' => new moodle_url('/message/index.php', array('id' => $data->id)), 'title' => get_string('sendmessageto', 'core_message', fullname($data))));
			$output .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/email'), 'class' => 'icon', 'alt' => get_string('sendmessageto', 'core_message', fullname($data))));
			$output .= html_writer::end_tag('a');
		}
		$output .= html_writer::end_tag('div');
		$output .= html_writer::end_tag('div');

		return $output;
	}

	function distribution_table_for_course($courseid) {

		// Count the number of distributions with a specific rating
		$distribution_data = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
		$memberships = memberships_per_course($courseid);
		foreach($memberships as $userid => $groups) {
			foreach($groups as $groupsid => $rating) {
				if(1 <= $rating and $rating <= 5) {
					// Increment the counter for users with this rating
					$distribution_data[$rating]++;
				}
			}
		}

		$distribution_row = array();
		$distribution_head = array();
		$rating_names = get_rating_names();
		foreach($distribution_data as $rating => $count) {
			$cell = new html_table_cell();
			$cell->text = $count;
			$cell->attributes['class'] = 'groupdistribution_rating_' . $rating_names[$rating];
			$distribution_row[$rating] = $cell;

			$cell = new html_table_cell();
			$cell->text = get_string('rating_' . $rating_names[$rating], 'groupdistribution');
			$distribution_head[$rating] = $cell;
		}

		$cell = new html_table_cell();
		$users_in_course = all_enrolled_users_in_course($courseid);
		$cell->text = count($users_in_course) - count($memberships);
		$distribution_row[] = $cell;

		$cell = new html_table_cell();
		$cell->text = get_string('unassigned_users', 'groupdistribution');
		$distribution_head[] = $cell;

		// The distribution table shows how many users got into a group with a
		// good/ok/bad... rating
		$distribution_table = new html_table();
		$distribution_table->data = array($distribution_row);
		$distribution_table->head = $distribution_head;

		$output = '';
		$output .= $this->box_start();
		$output .= get_string('distribution_table', 'groupdistribution');
		$output .= '<br><br>';
		$output .= html_writer::table($distribution_table);
		$output .= $this->box_end();
		
		return $output;
	}
}
