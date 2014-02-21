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
 * Contains unit tests for the distribution algorithm.
 *
 * @package    mod
 * @subpackage mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/groupdistribution/locallib.php');

class algorithm_test extends basic_testcase {

    function test_1() {
        $groups = array();
        $groups[1] = new stdClass();
        $groups[1]->maxsize = 2;
        $groups[1]->groupsid = 1;
        $groups[2] = new stdClass();
        $groups[2]->maxsize = 2;
        $groups[2]->groupsid = 2;

        $ratings = array();
        $ratings[1] = new stdClass();
        $ratings[1]->userid = 1;
        $ratings[1]->groupsid = 1;
        $ratings[1]->rating = 5;

        $ratings[2] = new stdClass();
        $ratings[2]->userid = 1;
        $ratings[2]->groupsid = 2;
        $ratings[2]->rating = 3;

        $ratings[3] = new stdClass();
        $ratings[3]->userid = 2;
        $ratings[3]->groupsid = 1;
        $ratings[3]->rating = 5;

        $ratings[4] = new stdClass();
        $ratings[4]->userid = 2;
        $ratings[4]->groupsid = 2;
        $ratings[4]->rating = 2;

        $ratings[5] = new stdClass();
        $ratings[5]->userid = 3;
        $ratings[5]->groupsid = 1;
        $ratings[5]->rating = 2;

        $ratings[6] = new stdClass();
        $ratings[6]->userid = 3;
        $ratings[6]->groupsid = 2;
        $ratings[6]->rating = 0;

        $ratings[7] = new stdClass();
        $ratings[7]->userid = 4;
        $ratings[7]->groupsid = 1;
        $ratings[7]->rating = 4;

        $ratings[8] = new stdClass();
        $ratings[8]->userid = 4;
        $ratings[8]->groupsid = 2;
        $ratings[8]->rating = 4;

        $ratings[9] = new stdClass();
        $ratings[9]->userid = 5;
        $ratings[9]->groupsid = 1;
        $ratings[9]->rating = 3;

        $usercount = 6;

        $distribution = compute_distribution($groups, $ratings, $usercount);
        $expected = array(1 => array(2, 5), 2 => array(4, 1));

        $this->assertEquals($distribution, $expected);
    }
}
