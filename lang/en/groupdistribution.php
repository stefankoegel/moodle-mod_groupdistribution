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
 * English strings for groupdistribution
 *
 * @package    mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'groupdistribution';
$string['modulenameplural'] = 'groupdistributions';
$string['modulename_help'] = 'Use the groupdistribution module for... | The groupdistribution module allows...'; //TODO
$string['groupdistributionname'] = 'Name';
$string['groupdistributionname_help'] = 'Help...'; //TODO
$string['groupdistribution'] = 'Groupdistribution';
$string['pluginadministration'] = 'Groupdistribution administration';
$string['pluginname'] = 'groupdistribution';
$string['group'] = 'Group';
$string['description_form'] = "Group's description";
$string['maxsize_form'] = 'Maximum number of users in group';
$string['rateable_form'] = 'Can users view and rate this group via Groupdistribution?';
$string['rate_group'] = "Please rate this group";
$string['rate_group_help'] = "The better the rating, the higher are your chance to be distributed in this group.";
$string['rating_impossible'] = "Impossible";
$string['rating_worst'] = "Worst";
$string['rating_bad'] = "Bad";
$string['rating_ok'] = "Ok";
$string['rating_good'] = "Good";
$string['rating_best'] = "Best";
$string['start_distribution'] = "Start distribution";
$string['clear_groups'] = 'Clear all groups';
$string['begindate'] = 'Rating begins at:';
$string['enddate'] = 'Rating ends at:';
$string['too_early_to_rate_1'] = 'You can not yet give your ratings. Come back between ';
$string['too_early_to_rate_2'] = ' and ';
$string['too_early_to_rate_3'] = '.';
$string['too_early_to_distribute_1'] = 'The rating period begins at ';
$string['too_early_to_distribute_2'] = ' and lasts until ';
$string['too_early_to_distribute_3'] = '. You can start the distribution after this period.';
$string['invalid_dates'] = 'The begin date must be before the end date!';
$string['start_distribution_explanation'] = 'Start the distribution process. This might take some time. Contatct your admin if php runs out of time (sry).';
$string['clear_groups_explanation'] = 'Remove all users from groups for which they gave a rating. If a user is in a group of the same course for which he didn\'t supply a rating (because it was not rateable) he will stay in that group.';
$string['view_distribution_table'] = 'Show the anonymized ratings of all users in a table.';
$string['show_table'] = 'Show table';
$string['unassigned_users'] = 'Users without a group';
$string['distribution_table'] = 'Shows how many users were distributed TODO';
$string['ratings_table'] = 'This table shows all ratings given by users.';
$string['ratings_saved'] = 'Your ratings have been saved.';
$string['no_rating_given'] = 'None';
$string['rating_is_over'] = 'The rating period has ended.';
$string['only_one_per_course'] = 'You can only have one groupdistribution per course!';
$string['timeout_field'] = 'Time in seconds until the algorithm gets stopped by php.';
$string['invalid_timeout'] = 'You need to enter a more reasonable number!';
$string['at_least_two'] = 'Please give at least two ratings better than impossible.';
$string['distribution_saved'] = 'Distribution saved.';
$string['groups_cleared'] = 'All groups cleared.';
