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

	function show_groupdistribution() {
		global $DB, $PAGE, $COURSE;

		$groups = $DB->get_records('groups', array('courseid' => $COURSE->id));
		$groupNames = array();
		foreach($groups as $group) {
			$groupNames[$group->id] = $group->name;
		}

		$groupdistribution = $DB->get_record('groupdistribution', array('courseid' => $COURSE->id));

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
		$tableData = array();
		foreach($ratings as $rating) {
			if(!array_key_exists($rating->userid, $tableData)) {
				$tableData[$rating->userid] = array();
			}
			$cell = new html_table_cell();
			$cell->text = get_string('rating_' . $ratingNames[$rating->rating], 'groupdistribution');
			$cell->attributes['class'] = 'groupdistribution_rating_' . $ratingNames[$rating->rating];
			if(array_key_exists($rating->userid, $memberships)
				and array_key_exists($rating->groupsid, $memberships[$rating->userid])) {
				$cell->attributes['class'] .= ' groupdistribution_member';
			}

			$tableData[$rating->userid][$rating->groupsid] = $cell;
		}

		$table = new html_table();
		$table->data = $tableData;
		$table->head = $groupNames;

		$startURL = new moodle_url($PAGE->url, array('action' => ACTION_START));
		$clearURL = new moodle_url($PAGE->url, array('action' => ACTION_CLEAR));

		$output = '';
		$output .= $this->box_start();

		if($groupdistribution->enddate < time()) {
			$output .= $this->box_start();
			$output .= $this->single_button($startURL,
				get_string('start_distribution', 'groupdistribution'));
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

		$output .= $this->box_start('groupdistribution_ratings_box');
		$output .= html_writer::table($table);
		$output .= $this->box_end();
		$output .= $this->box_end();
		return $output;
	}
}
