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
 * @package    mod
 * @subpackage mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/renderer.php');

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
        global $COURSE, $PAGE, $USER, $DB;

        $mform = $this->_form;

        $ratingdata = get_rating_data_for_user_in_course($COURSE->id, $USER->id);

        $renderer = $PAGE->get_renderer('mod_groupdistribution');

        $mform->addElement('hidden', 'action', ACTION_RATE);
        $mform->setType('action', PARAM_TEXT);

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        foreach ($ratingdata as $data) {
            $headerelem      = 'head_groupdistribution_' . $data->groupsid;
            $elemprefix      = 'data[' . $data->groupsid . ']';
            $ratingelem      = $elemprefix . '[rating]';
            $groupsidelem    = $elemprefix . '[groupsid]';

            $mform->addElement('hidden', $groupsidelem, $data->groupsid);
            $mform->setType($groupsidelem, PARAM_INT);

            $mform->addElement('header', $headerelem, $data->name);
            $mform->setExpanded($headerelem);

            $group = $DB->get_record('groups', array('id' => $data->groupsid));
            $mform->addElement('html', $renderer->format_group($group, false));

            // The higher the rating, the greater the desire to get into this group
            $options = array(
                5 => get_string('rating_5', 'groupdistribution'),
                4 => get_string('rating_4', 'groupdistribution'),
                3 => get_string('rating_3', 'groupdistribution'),
                2 => get_string('rating_2', 'groupdistribution'),
                1 => get_string('rating_1', 'groupdistribution'),
                0 => get_string('rating_0', 'groupdistribution'));

            // If there is a valid value in the databse, choose the according rating
            // from the dropdown.
            // Else use a default value.
            if (is_numeric($data->rating) and $data->rating >= 0 and $data->rating <= 5) {
                $mform->addElement('select', $ratingelem, get_string('rate_group', 'groupdistribution'), $options);
                $mform->setDefault($ratingelem, $data->rating);
            } else {
                $mform->addElement('select', $ratingelem, get_string('rate_group_not_saved', 'groupdistribution'), $options);
                $mform->setDefault($ratingelem, 3); // default: ok (3)
            }
            $mform->setType($ratingelem, PARAM_INT);
        }
        // If there are no groups to rate, notify the user.
        if (count($ratingdata) > 0) {
            $this->add_action_buttons();
        } else {
            $box = $renderer->notification(get_string('no_groups_to_rate', 'groupdistribution'));
            $mform->addElement('html', $box);
        }
    }

    /**
     * Returns the forms HTML code. So we don't have to call display().
     */
    public function to_html() {
        return $this->_form->toHtml();
    }

    /**
     * Make sure that users give at least two ratings better than 'impossible' (0).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!array_key_exists('data', $data) or count($data['data']) < 2) {
            return $errors;
        }

        $possibles = 0;
        $ratings = $data['data'];
        foreach ($ratings as $rating) {
            if ($rating['rating'] > 0) {
                $possibles++;
            }
        }
        if ($possibles < 2) {
            foreach ($ratings as $gid => $rating) {
                if ($rating['rating'] == 0) {
                    $errors['data[' . $gid . '][rating]'] = get_string('at_least_two', 'groupdistribution');
                }
            }
        }
        return $errors;
    }
}
