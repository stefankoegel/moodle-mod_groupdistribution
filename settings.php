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

$settings->add(new admin_setting_configtext('groupdistribution_max_timeout',
	get_string('max_timeout', 'groupdistribution'),
	get_string('max_timeout_description', 'groupdistribution'),
	600, PARAM_INT));

$settings->add(new admin_setting_configtext('groupdistribution_maxsize',
	get_string('maxsize_setting', 'groupdistribution'),
	get_string('maxsize_setting_description', 'groupdistribution'),
	15, PARAM_INT));
