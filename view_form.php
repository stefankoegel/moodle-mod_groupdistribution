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

/**
 * Module instance settings form
 */
class mod_groupdistribution_view_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $DB, $COURSE, $USER, $CFG;

        $mform = $this->_form;

        $sql = "SELECT g.id, g.id AS courseid, gd.groupsid, g.description, g.name, gd.israteable, gdrs.rating, gdrs.id AS ratingid
                  FROM {groups} AS g
                  JOIN {groupdistribution_data} AS gd
                    ON g.id = gd.groupsid
             LEFT JOIN {groupdistribution_ratings} AS gdrs
                    ON g.id = gdrs.groupsid
                 WHERE g.courseid = :courseid AND (gdrs.userid = :userid OR gdrs.userid IS NULL)";
        $ratings_in_course = $DB->get_records_sql($sql, array('courseid' => $COURSE->id, 'userid' => $USER->id));

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'action', ACTION_RATE);
        $mform->setType('action', PARAM_TEXT);

        foreach($ratings_in_course as $group_and_rating) {
            $header_elem      = "head_groupdistribution_$group_and_rating->groupsid";
            $elem_prefix      = "data[$group_and_rating->groupsid]";
            // $groupdataid_elem = $elem_prefix . '[groupdataid]';
            $rating_elem      = $elem_prefix . '[rating]';
            $ratingid_elem    = $elem_prefix . '[ratingid]';
            // $groupid_elem     = $elem_prefix . '[groupid]';

            $mform->addElement('header', $header_elem, get_string('group', 'groupdistribution') . ': ' . $group_and_rating->name);
            $mform->setExpanded($header_elem);

            $description_box  = '<div class="groupdistribution_description_box">';
            $description_box .= format_text($group_and_rating->description);
            $description_box .= '</div>';
            $mform->addElement('html', $description_box);

            $mform->addElement('hidden', $ratingid_elem, $group_and_rating->ratingid);
            $mform->setType($ratingid_elem, PARAM_INT);

            // $mform->addElement('hidden', $groupid_elem, $group_and_rating->groupsid);
            // $mform->setType($groupid_elem, PARAM_INT);

            // the higher the rating, the greater the desire to get into this group_and_rating
            $options = array(
                0 => get_string('rating_impossible', 'groupdistribution'),
                1 => get_string('rating_worst', 'groupdistribution'),
                2 => get_string('rating_bad', 'groupdistribution'),
                3 => get_string('rating_ok', 'groupdistribution'),
                4 => get_string('rating_good', 'groupdistribution'),
                5 => get_string('rating_best', 'groupdistribution'));
            $mform->addElement('select', $rating_elem, get_string('rate_group', 'groupdistribution'), $options);
            $mform->setType($rating_elem, PARAM_INT);

            if(is_numeric($group_and_rating->rating) and $group_and_rating->rating >= 0 and $group_and_rating->rating <= 5) {
                $mform->setDefault($rating_elem, $group_and_rating->rating);
            } else {
                $mform->setDefault($rating_elem, 3);
            }

            // vllt. geht das gleiche auch mit gd_data
        }
        $this->add_action_buttons();
    }

    // http://docs.moodle.org/dev/lib/formslib.php_Validation
    public function validation($a, $b) {
        return array();
    }
}
