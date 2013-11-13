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

$string['at_least_two'] = 'Please give at least two ratings better than impossible.';
$string['at_least_two_groups'] = 'A course must have at least two groups to add a groupdistribution activity.';
$string['begindate'] = 'Rating begins at:';
$string['changes'] = 'There have been {$a} change(s)! Please review your ratings.';
$string['clear_groups'] = 'Clear all groups';
$string['clear_groups_explanation'] = 'Remove all users from groups for which they gave a rating. If a user is in a group of the same course for which he didn\'t supply a rating (because it was not rateable) he will stay in that group.';
$string['description_form'] = "Group's description";
$string['description_overrides'] = 'Overrides group description';
$string['description_overrides_help'] = 'This field shows the groups description. When you save your changes, the groups description will be overriden with the content of this field.';
$string['distribution_saved'] = 'Distribution saved.';
$string['distribution_table'] = 'Shows how many users got into a group with a specific rating.';
$string['enddate'] = 'Rating ends at:';
$string['global_max_size'] = 'Sets the maximum number of users of all groups to this value.';
$string['group'] = 'Group';
$string['group_not_in_course'] = 'One of the groups does not belong to this course.';
$string['groupdistribution'] = 'Groupdistribution';
$string['groupdistribution:give_rating'] = 'Give ratings for groups';
$string['groupdistribution:start_distribution'] = 'Start distribution algorithm';
$string['groupdistributionname'] = 'Name';
$string['groups_cleared'] = 'All groups cleared.';
$string['invalid_dates'] = 'The begin date must be before the end date!';
$string['invalid_path'] = 'Invalid path!';
$string['max_timelimit'] = 'Groupdistribution algorithm time limit in seconds';
$string['max_timelimit_description'] = 'This controls after how many seconds the distribution algorithm gets interrupted by php. If teachers need more time to compute their distributions you can increase this value.';
$string['maxsize_form'] = 'Maximum number of users in group';
$string['maxsize_setting'] = 'Default maximum number of users per group';
$string['maxsize_setting_description'] = 'This is the default value for the maximum number of users in the groupdistribution settings form.';
$string['modulename'] = 'Groupdistribution';
$string['modulename_help'] = 'Groupdistributin allows users give ratings to groups during a rating period. After this period is over an algorithm will distribute the users according to their ratings into the groups.';
$string['modulenameplural'] = 'Groupdistributions';
$string['negative_cycle'] = 'Negative cycle detected!';
$string['no_groups_to_rate'] = 'There are no groups for you to rate. Ask your teacher to add some.';
$string['no_rating_given'] = 'None';
$string['only_one_per_course'] = 'You can only have one groupdistribution per course!';
$string['pluginadministration'] = 'Groupdistribution administration';
$string['pluginname'] = 'Groupdistribution';
$string['rate_group'] = "Please rate this group";
$string['rate_group_help'] = "The better the rating, the higher are your chance to be distributed in this group.";
$string['rateable_form'] = 'Can users view and rate this group via Groupdistribution?';
$string['rating_bad'] = "Bad";
$string['rating_best'] = "Best";
$string['rating_good'] = "Good";
$string['rating_impossible'] = "Impossible";
$string['rating_is_over'] = 'The rating period has ended.';
$string['rating_ok'] = "Ok";
$string['rating_worst'] = "Worst";
$string['ratings_saved'] = 'Your ratings have been saved.';
$string['ratings_table'] = 'This table shows all ratings given by users.';
$string['set_max_size_button'] = 'Set the values';
$string['show_names'] = 'Show user names in ratings table';
$string['show_names_description'] = 'You can protect users privacy by hiding their names and e-mail addresses in the ratings table. This prevents teachers from knowing about the users ratings.';
$string['show_rating_period'] = 'The rating period begins at {$a->begin} and lasts until {$a->end}.';
$string['show_table'] = 'Show ratings table';
$string['start_distribution'] = "Start distribution";
$string['start_distribution_explanation'] = 'Start the distribution process. This might take some time.';
$string['too_early_to_distribute'] = 'You can start the distribution after the rating period has ended.';
$string['too_early_to_rate'] = 'You can not yet give your ratings. Please come back during the rating period.';
$string['unassigned_users'] = 'Users without a group';
$string['view_distribution_table'] = 'Show the anonymized ratings of all users in a table.';
