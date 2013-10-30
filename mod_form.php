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
 * The main groupdistribution configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('locallib.php');

/**
 * Module instance settings form
 */
class mod_groupdistribution_mod_form extends moodleform_mod {

	/**
	 * Defines forms elements
	 */
	public function definition() {
		global $DB, $COURSE, $PAGE, $CFG;

		$mform = $this->_form;

		// Allow only one groupdistribution per course
		// See: https://moodle.org/mod/forum/discuss.php?d=205664
		// And: https://github.com/SWiT/moodle-internal-course-email/blob/master/mod/email/mod_form.php

		$is_update = optional_param('update', 0, PARAM_INT);
		$already_exists = $DB->record_exists('groupdistribution', array('courseid' => $COURSE->id));

		if($already_exists and $is_update == 0) {
			$renderer = $PAGE->get_renderer('mod_groupdistribution');
			$mform->addElement('html',
				$renderer->error_text(get_string('only_one_per_course', 'groupdistribution')));

			$this->standard_hidden_coursemodule_elements();
			return;
		}

		// There must be at least two groups
		if($DB->count_records('groups', array('courseid' => $COURSE->id)) < 2) {
			$renderer = $PAGE->get_renderer('mod_groupdistribution');
			$mform->addElement('html',
				$renderer->error_text(get_string('at_least_two_groups', 'groupdistribution')));

			$this->standard_hidden_coursemodule_elements();
			return;
		}

		$PAGE->requires->js_init_call('M.mod_groupdistribution.init');

		//-------------------------------------------------------------------------------
		// Adding the "general" fieldset, where all the common settings are showed
		$mform->addElement('header', 'general', get_string('general', 'form'));

		// Adding the standard "name" field
		$mform->addElement('text', 'name', get_string('groupdistributionname', 'groupdistribution'), array('size'=>'64'));
		if (!empty($CFG->formatstringstriptags)) {
			$mform->setType('name', PARAM_TEXT);
		} else {
			$mform->setType('name', PARAM_CLEAN);
		}
		$mform->addRule('name', null, 'required', null, 'client');
		$mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');


		// Adding the standard "intro" and "introformat" fields
		$this->add_intro_editor();

		$mform->addElement('date_time_selector', 'begindate', get_string('begindate', 'groupdistribution'));
		$mform->addElement('date_time_selector', 'enddate', get_string('enddate', 'groupdistribution'));


		// Check if values for begindate and enddate exist in the database.
		// If not, use default values.
		if($DB->record_exists('groupdistribution', array('courseid' => $COURSE->id))) {
			$groupdistribution = $DB->get_record('groupdistribution', array('courseid' => $COURSE->id));

			$mform->setDefault('begindate', $groupdistribution->begindate);
			$mform->setDefault('enddate', $groupdistribution->enddate);
		} else {
			$mform->setDefault('begindate', time() + 24 * 60 * 60); // default: tomorrow
			$mform->setDefault('enddate', time() + 7 * 24 * 60 * 60); // default: now + one week
		}

		$mform->addElement('hidden', 'courseid', $COURSE->id);
		$mform->setType('courseid', PARAM_INT);

		$size_group = array();
		$size_group[] = $mform->createElement('text', 'global_max_size', '', array('id' => 'global_max_size'));
		$size_group[] = $mform->createElement('button', 'set_max_size_button',
			get_string('set_max_size_button', 'groupdistribution'), array('id' => 'set_max_size_button'));
		$mform->addGroup($size_group, 'size_group', get_string('global_max_size', 'groupdistribution'));
		$mform->setType('size_group[global_max_size]', PARAM_INT);

		//-------------------------------------------------------------------------------
		// Important settings for groupdistribution.
		// Choose the groups between which the users can choose and set their maximum size.

		$groups_in_course = $DB->get_records('groups', array('courseid' => $COURSE->id));

		$editoroptions = array(
				'collapsible' => 1,
				'collapsed' => 1,
				'maxfiles' => EDITOR_UNLIMITED_FILES,
				'maxbytes'=> $CFG->maxbytes,
				'trusttext'=> false,
				'noclean'=>true);

		foreach($groups_in_course as $group) {
			$elem_prefix = "data[$group->id]";

			$header_elem = "head_groupdistribution_$group->id";
			$description_elem = $elem_prefix . "[description]";
			$maxsize_elem = $elem_prefix . "[maxsize]";
			$israteable_elem = $elem_prefix . "[rateable]";
			$groupdataid_elem = $elem_prefix . "[groupdataid]";
			$groupsid_elem = $elem_prefix . "[groupsid]";

			$mform->addElement('header', $header_elem, get_string('group', 'groupdistribution') . ': ' . $group->name);
			$mform->setExpanded($header_elem);

			$mform->addElement('editor', $description_elem, get_string('description_form', 'groupdistribution'),
					null, $editoroptions);
			$mform->setType($description_elem, PARAM_RAW);
			$mform->setDefault($description_elem, array('text' => $group->description));
			$mform->addHelpButton($description_elem, 'description_overrides', 'groupdistribution');


			$mform->addElement('text', $maxsize_elem, get_string('maxsize_form', 'groupdistribution'),
				array('id' => 'max_size_field'));
			$mform->setType($maxsize_elem, PARAM_INT);

			$mform->addElement('selectyesno', $israteable_elem, get_string('rateable_form', 'groupdistribution'));

			// Check if there is an entry in groupdistribution_data for this group
			// and enter its values into the form.
			// If there is none, use default values.
			if($DB->record_exists('groupdistribution_data', array('groupsid' => $group->id))) { 
				$data = $DB->get_record('groupdistribution_data', array('groupsid' => $group->id));

				$mform->setDefault($maxsize_elem, $data->maxsize);
				$mform->setDefault($israteable_elem, $data->israteable);

				$mform->addElement('hidden', $groupdataid_elem, $data->id);
				$mform->setType($groupdataid_elem, PARAM_INT);

			} else {
				$mform->setDefault($maxsize_elem, $CFG->groupdistribution_maxsize); // default: $CFG->groupdistribution_maxsize
				$mform->setDefault($israteable_elem, 1); // default: yes (1)
			}

			$mform->addElement('hidden', $groupsid_elem, $group->id);
			$mform->setType($groupsid_elem, PARAM_INT);
		}

		//-------------------------------------------------------------------------------
		// add standard elements, common to all modules
		$this->standard_coursemodule_elements();
		//-------------------------------------------------------------------------------
		// add standard buttons, common to all modules
		$this->add_action_buttons();
	}

	/**
	 * Checks that begindate is before enddate and that there is
	 * only one groupdistribution per course.
	 */
	public function validation($data, $files) {
		$errors = parent::validation($data, $files);

		if($data['enddate'] <= $data['begindate']) {
			$errors['begindate'] = get_string('invalid_dates', 'groupdistribution');
		}
		return $errors;
	}
}
