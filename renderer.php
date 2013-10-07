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

require_once($CFG->dirroot . '/mod/groupdistribution/view_form.php');
require_once('locallib.php');

class mod_groupdistribution_renderer extends plugin_renderer_base {

	function display_user_rating_form() {
		global $DB, $COURSE;

		$groupdistribution = $DB->get_record('groupdistribution', array('courseid' => $COURSE->id));

		if(time() < $groupdistribution->begindate) {
			$output  = get_string('too_early_to_rate_1', 'groupdistribution');
			$output .= userdate($groupdistribution->begindate);
			$output .= get_string('too_early_to_rate_2', 'groupdistribution');
			$output .= userdate($groupdistribution->enddate);
			$output .= get_string('too_early_to_rate_3', 'groupdistribution');
			return $this->notification($output);
		}
		if($groupdistribution->enddate < time()) {
			return $this->box(get_string('rating_is_over', 'groupdistribution'));
		}

		$mform = new mod_groupdistribution_view_form();
		$mform->display();
	}

	function show_controls() {
		global $PAGE, $COURSE, $DB;

		$startURL = new moodle_url($PAGE->url, array('action' => ACTION_START));
		$clearURL = new moodle_url($PAGE->url, array('action' => ACTION_CLEAR));
		$tableURL = new moodle_url($PAGE->url, array('action' => SHOW_TABLE));

		$groupdistribution = $DB->get_record('groupdistribution', array('courseid' => $COURSE->id));

		$output = '';
		$output .= $this->box_start();

		if($groupdistribution->enddate < time()) {
			$output .= $this->box_start();
			$output .= get_string('start_distribution_explanation', 'groupdistribution');
			$output .= $this->single_button($startURL,
					get_string('start_distribution', 'groupdistribution'));
			$output .= $this->box_end();

			$output .= $this->box_start();
			$output .= get_string('clear_groups_explanation', 'groupdistribution');
			$output .= $this->single_button($clearURL,
					get_string('clear_groups', 'groupdistribution'));
			$output .= $this->box_end();
		} else {
			$output .= get_string('rating_period_1', 'groupdistribution');
			$output .= userdate($groupdistribution->begindate);
			$output .= get_string('rating_period_2', 'groupdistribution');
			$output .= userdate($groupdistribution->enddate);
			$output .= get_string('rating_period_3', 'groupdistribution');
		}

		$output .= $this->box_start();
		$output .= get_string('view_distribution_table', 'groupdistribution');
		$output .= $this->single_button($tableURL,
				get_string('show_table', 'groupdistribution'));
		$output .= $this->box_end();

		$output .= $this->box_end();
		return $output;
	}

	function show_groupdistribution() {
		global $DB, $PAGE, $COURSE;

		$groups = $DB->get_records('groups', array('courseid' => $COURSE->id));
		$groupNames = array();
		foreach($groups as $group) {
			$groupNames[$group->id] = $group->name;
		}

		$ratingNames = array(
			'impossible',
			'worst',
			'bad',
			'ok',
			'good',
			'best');

		// I noticed no speedup with get_recordSET. Keeping the simpler version.
		$ratings = $DB->get_records('groupdistribution_ratings', array('courseid' => $COURSE->id));
		$memberships = memberships_per_course($COURSE->id);

		$ratings_cells = array();
		$distribution_data = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0, 0 => 0);
		foreach($ratings as $rating) {
			// Create a cell in the table for each rating
			if(!array_key_exists($rating->userid, $ratings_cells)) {
				$ratings_cells[$rating->userid] = array();
			}
			$cell = new html_table_cell();
			$cell->text = get_string('rating_' . $ratingNames[$rating->rating], 'groupdistribution');
			$cell->attributes['class'] = 'groupdistribution_rating_' . $ratingNames[$rating->rating];

			// Check if the user has been distributed along this rating
			if(array_key_exists($rating->userid, $memberships)
				and array_key_exists($rating->groupsid, $memberships[$rating->userid])) {
				// Highlight the cell/rating
				$cell->attributes['class'] .= ' groupdistribution_member';

				$distribution_data[$rating->rating]++;
			}

			$ratings_cells[$rating->userid][$rating->groupsid] = $cell;
		}
		$ratings_table = new html_table();
		$ratings_table->data = $ratings_cells;
		$ratings_table->head = $groupNames;

		$distribution_row = array();
		$distribution_head = array();
		foreach($distribution_data as $rating => $count) {
			$cell = new html_table_cell();
			$cell->text = $count;
			$cell->attributes['class'] = 'groupdistribution_rating_' . $ratingNames[$rating];
			$distribution_row[$rating] = $cell;

			$cell = new html_table_cell();
			$cell->text = get_string('rating_' . $ratingNames[$rating], 'groupdistribution');
			$distribution_head[$rating] = $cell;
		}
		$cell = new html_table_cell();
		$cell->text = count_users_with_ratings($COURSE->id) - count($memberships);
		$distribution_row[] = $cell;
		$cell = new html_table_cell();
		$cell->text = get_string('unassigned_users', 'groupdistribution');
		$distribution_head[] = $cell;

		$distribution_table = new html_table();
		$distribution_table->data = array($distribution_row);
		$distribution_table->head = $distribution_head;

		$output = '';
		$output .= $this->box_start();
		$output .= $this->box_start();
		$output .= get_string('distribution_table', 'groupdistribution');
		$output .= '<br>';
		$output .= html_writer::table($distribution_table);
		$output .= $this->box_end();

		$output .= $this->box_start();
		$output .= get_string('ratings_table', 'groupdistribution');
		$output .= '<br>';
		$output .= $this->box(html_writer::table($ratings_table), 'groupdistribution_ratings_box');
		$output .= $this->box_end();

		$output .= $this->box_end();

		return $output;
	}
}
