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

        $isupdate = optional_param('update', 0, PARAM_INT);
        $alreadyexists = $DB->record_exists('groupdistribution', array('course' => $COURSE->id));

        if ($alreadyexists and $isupdate == 0) {
            $renderer = $PAGE->get_renderer('mod_groupdistribution');
            $mform->addElement('html',
                $renderer->error_text(get_string('only_one_per_course', 'groupdistribution')));

            $this->standard_hidden_coursemodule_elements();
            return;
        }

        // There must be at least two groups
        if ($DB->count_records('groups', array('courseid' => $COURSE->id)) < 2) {
            $renderer = $PAGE->get_renderer('mod_groupdistribution');
            $mform->addElement('html',
                $renderer->error_text(get_string('at_least_two_groups', 'groupdistribution')));

            $this->standard_hidden_coursemodule_elements();
            return;
        }

        $PAGE->requires->js_init_call('M.mod_groupdistribution.init');

        // -------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('groupdistributionname', 'groupdistribution'), array('size' => '64'));
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
        if ($DB->record_exists('groupdistribution', array('course' => $COURSE->id))) {
            $groupdistribution = $DB->get_record('groupdistribution', array('course' => $COURSE->id));

            $mform->setDefault('begindate', $groupdistribution->begindate);
            $mform->setDefault('enddate', $groupdistribution->enddate);
        } else {
            $mform->setDefault('begindate', time() + 24 * 60 * 60); // default: tomorrow
            $mform->setDefault('enddate', time() + 7 * 24 * 60 * 60); // default: now + one week
        }

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $sizegroup = array();
        $sizegroup[] = $mform->createElement('text', 'global_max_size', '', array('id' => 'global_max_size'));
        $sizegroup[] = $mform->createElement('button', 'set_max_size_button',
            get_string('set_max_size_button', 'groupdistribution'),
            array('id' => 'set_max_size_button'));
        $mform->addGroup($sizegroup, null, get_string('global_max_size', 'groupdistribution'), null, false);
        $mform->setType('global_max_size', PARAM_INT);

        // -------------------------------------------------------------------------------
        // Important settings for groupdistribution.
        // Choose the groups between which the users can choose and set their maximum size.

        $groupsincourse = $DB->get_records('groups', array('courseid' => $COURSE->id));

        $editoroptions = array(
                'collapsible' => 1,
                'collapsed' => 1,
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'maxbytes' => $CFG->maxbytes,
                'trusttext' => false,
                'noclean' => true);

        foreach ($groupsincourse as $group) {
            $elemprefix = "data[$group->id]";

            $headerelem = "head_groupdistribution_$group->id";
            $descriptionelem = $elemprefix . "[description]";
            $maxsizeelem = $elemprefix . "[maxsize]";
            $israteableelem = $elemprefix . "[rateable]";
            $groupdataidelem = $elemprefix . "[groupdataid]";
            $groupsidelem = $elemprefix . "[groupsid]";

            $mform->addElement('header', $headerelem, get_string('group', 'groupdistribution') . ': ' . $group->name);

            // Checkbox
            $mform->addElement('selectyesno', $israteableelem, get_string('rateable_form', 'groupdistribution'));

            // Textfield
            $mform->addElement('text', $maxsizeelem, get_string('maxsize_form', 'groupdistribution'),
                array('id' => 'max_size_field'));
            $mform->setType($maxsizeelem, PARAM_INT);
            $mform->addRule($maxsizeelem, null, 'numeric', null, 'client');
            // $mform->disabledIf($maxsizeelem, $israteableelem, 'eq', 0);

            // Editor
            $mform->addElement('editor', $descriptionelem, get_string('description_form', 'groupdistribution'),
                    null, $editoroptions);
            $mform->setType($descriptionelem, PARAM_RAW);
            $mform->setDefault($descriptionelem, array('text' => $group->description));
            $mform->addHelpButton($descriptionelem, 'description_overrides', 'groupdistribution');
            // Does not work for editor elements :(
            // $mform->disabledIf($descriptionelem, $israteableelem, 'eq', 0);

            // Check if there is an entry in groupdistribution_data for this group
            // and enter its values into the form.
            // If there is none, use default values.
            if ($DB->record_exists('groupdistribution_data', array('groupsid' => $group->id))) {
                $data = $DB->get_record('groupdistribution_data', array('groupsid' => $group->id));

                $mform->setDefault($maxsizeelem, $data->maxsize);
                $mform->setDefault($israteableelem, $data->israteable);

                // Expand if rateable
                if ($data->israteable == 1) {
                    $mform->setExpanded($headerelem);
                }

                $mform->addElement('hidden', $groupdataidelem, $data->id);
                $mform->setType($groupdataidelem, PARAM_INT);

            } else {
                $mform->setDefault($maxsizeelem, $CFG->groupdistribution_maxsize); // default: $CFG->groupdistribution_maxsize
                $mform->setDefault($israteableelem, 0); // default: no (0)
                // Always expand new group data
                $mform->setExpanded($headerelem);
            }

            $mform->addElement('hidden', $groupsidelem, $group->id);
            $mform->setType($groupsidelem, PARAM_INT);
        }

        // -------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        // -------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }

    /**
     * Checks that begindate is before enddate and that there is
     * only one groupdistribution per course.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['enddate'] <= $data['begindate']) {
            $errors['begindate'] = get_string('invalid_dates', 'groupdistribution');
        }

        // This form is only visible if there are at least two groups.
        // So we don't need to check this.
        $count = 0;
        foreach ($data['data'] as $group) {
            if ($group['rateable'] > 0) {
                $count++;
            }
        }
        if ($count < 2) {
            foreach ($data['data'] as $id => $group) {
                if ($group['rateable'] <= 0) {
                    $errors['data[' . $id . '][rateable]'] =
                    get_string('at_least_two_rateable_groups', 'groupdistribution');
                }
            }
        }

        return $errors;
    }
}
