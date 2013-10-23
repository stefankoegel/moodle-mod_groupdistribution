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
 * Form for users giving ratings.
 *
 * @package    mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once('locallib.php');
require_once('renderer.php');

/**
 * _Users view_
 * For every group for which the user can give a rating:
 * - shows the groups name and description
 * - shows a drop down menu from which the user can choose a rating 
 */
class mod_groupdistribution_view_form extends moodleform {

	/**
	 * Defines forms elements
	 */
	public function definition() {
		global $COURSE, $PAGE, $USER;

		$mform = $this->_form;

		$rating_data = get_rating_data_for_user_in_course($COURSE->id, $USER->id);

		$renderer = $PAGE->get_renderer('mod_groupdistribution');

		$mform->addElement('hidden', 'action', ACTION_RATE);
		$mform->setType('action', PARAM_TEXT);

		$mform->addElement('hidden', 'courseid', $COURSE->id);
		$mform->setType('courseid', PARAM_INT);

		foreach($rating_data as $data) {
			$header_elem      = "head_groupdistribution_$data->groupsid";
			$elem_prefix      = "data[$data->groupsid]";
			$rating_elem      = $elem_prefix . '[rating]';
			$groupsid_elem    = $elem_prefix . '[groupsid]';

			$mform->addElement('hidden', $groupsid_elem, $data->groupsid);
			$mform->setType($groupsid_elem, PARAM_INT);

			$mform->addElement('header', $header_elem, get_string('group', 'groupdistribution') . ': ' . $data->name);
			$mform->setExpanded($header_elem);

			$description_box = $renderer->box(format_text($data->description));
			$mform->addElement('html', $description_box);

			// The higher the rating, the greater the desire to get into this group
			$options = array(
				0 => get_string('rating_impossible', 'groupdistribution'),
				1 => get_string('rating_worst', 'groupdistribution'),
				2 => get_string('rating_bad', 'groupdistribution'),
				3 => get_string('rating_ok', 'groupdistribution'),
				4 => get_string('rating_good', 'groupdistribution'),
				5 => get_string('rating_best', 'groupdistribution'));
			$mform->addElement('select', $rating_elem, get_string('rate_group', 'groupdistribution'), $options);
			$mform->setType($rating_elem, PARAM_INT);

			// If there is a valid value in the databse, choose the according rating
			// from the dropdown.
			// Else use a default value.
			if(is_numeric($data->rating) and $data->rating >= 0 and $data->rating <= 5) {
				$mform->setDefault($rating_elem, $data->rating);
			} else {
				$mform->setDefault($rating_elem, 3); // default: ok (3)
			}
		}
		// If there are no groups to rate, notify the user.
		if(count($rating_data) > 0) {
			$this->add_action_buttons();
		} else {
			$box = $renderer->notification(get_string('no_groups_to_rate', 'groupdistribution'));
			$mform->addElement('html', $box);
		}
	}

	/**
	 * Returns the forms HTML code. So we don't have to call display().
	 */
	public function toHtml() {
		return $this->_form->toHtml();
	}

	/**
	 * Make sure that users give at least two ratings better than 'impossible' (0).
	 */
	public function validation($data, $files) {
		$errors = parent::validation($data, $files);

		if(!array_key_exists('data', $data) or count($data['data'] < 2)) {
			return $errors;
		}

		$possibles = 0;
		$ratings = $data['data'];
		foreach($ratings as $rating) {
			if($rating['rating'] > 0) {
				$possibles++;
			}
		}
		if($possibles < 2) {
			foreach($ratings as $gid => $rating) {
				if($rating['rating'] == 0) {
					$errors['data[' . $gid . '][rating]'] = get_string('at_least_two', 'groupdistribution');
				}
			}
		}
		return $errors;
	}
}
