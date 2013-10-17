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
 * Form for admin starting the distribution process.
 *
 * @package    mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once('locallib.php');

/**
 * Contains the button to start a distribution and to set the time limit
 * for the distribution algorithm.
 */
class mod_groupdistribution_start_form extends moodleform {

	/**
	 * Defines forms elements
	 */
	public function definition() {
		global $COURSE;

		$mform = $this->_form;

		$mform->addElement('hidden', 'courseid', $COURSE->id);
		$mform->setType('courseid', PARAM_INT);

		$mform->addElement('hidden', 'action', ACTION_START);
		$mform->setType('action', PARAM_TEXT);

		$mform->addElement('text', 'timeout', get_string('timeout_field', 'groupdistribution'), 'size="10"');
		$mform->setType('timeout', PARAM_INT);
		$mform->setDefault('timeout', 30);
		$mform->addHelpButton('timeout', 'timeout_field', 'groupdistribution');

		$mform->addElement('submit', 'submitbutton', get_string('start_distribution', 'groupdistribution'));
	}

	/**
	 * Returns the forms HTML code. So we don't have to call display().
	 */
	public function toHtml() {
		return $this->_form->toHtml();
	}

	/**
	 * Makes sure that the time limit for the algorithm is a reasonable value.
	 */
	public function validation($data, $files) {
		$errors = parent::validation($data, $files);
		
		if($data['timeout'] < 0 or $data['timeout'] > 600) {
			$errors['timeout'] = get_string('invalid_timeout', 'groupdistribution');
		}
		return $errors;
	}
}

