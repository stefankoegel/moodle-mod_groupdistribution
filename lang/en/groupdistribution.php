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

$string['modulename'] = 'Groupdistribution';
$string['modulenameplural'] = 'Groupdistributions';
$string['modulename_help'] = 'Groupdistributin allows users give ratings to groups during a rating period. After this period is over an algorithm will distribute the users according to their ratings into the groups.';
$string['groupdistributionname'] = 'Name';
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
$string['too_early_to_rate'] = 'You can not yet give your ratings. Please come back between {$a->begin} and {$a->end}.';
$string['too_early_to_distribute'] = 'The rating period begins at {$a->begin} and lasts until {$a->end}. You can start the distribution after this period.';
$string['invalid_dates'] = 'The begin date must be before the end date!';
$string['start_distribution_explanation'] = 'Start the distribution process. This might take some time.';
$string['clear_groups_explanation'] = 'Remove all users from groups for which they gave a rating. If a user is in a group of the same course for which he didn\'t supply a rating (because it was not rateable) he will stay in that group.';
$string['view_distribution_table'] = 'Show the anonymized ratings of all users in a table.';
$string['show_table'] = 'Show tables';
$string['unassigned_users'] = 'Users without a group';
$string['distribution_table'] = 'Shows how many users got into a group with a specific rating.';
$string['ratings_table'] = 'This table shows all ratings given by users.';
$string['ratings_saved'] = 'Your ratings have been saved.';
$string['no_rating_given'] = 'None';
$string['rating_is_over'] = 'The rating period has ended.';
$string['only_one_per_course'] = 'You can only have one groupdistribution per course!';
$string['timeout_field'] = 'Time in seconds until the algorithm gets stopped.';
$string['invalid_timeout'] = 'You need to enter a more reasonable number!';
$string['at_least_two'] = 'Please give at least two ratings better than impossible.';
$string['distribution_saved'] = 'Distribution saved.';
$string['groups_cleared'] = 'All groups cleared.';
$string['group_not_in_course'] = 'One of the groups does not belong to this course.';
$string['invalid_path'] = 'Invalid path!';
$string['negative_cycle'] = 'Negative cycle detected!';
$string['groupdistribution:start_distribution'] = 'Start distribution algorithm';
$string['groupdistribution:give_rating'] = 'Give ratings for groups';
