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
 * @package    mod
 * @subpackage mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['at_least_two'] = 'Please give at least two ratings better than "impossible".';
$string['at_least_two_groups'] = 'A course must have at least two groups for the groupdistribution activity to work.<br />You are redirected to the group configuration page where you can create new groups';
$string['at_least_two_rateable_groups'] = 'A course must have at least two rateable groups.';
$string['begindate'] = 'Rating begins at:';
$string['changes'] = 'Since {$a->time}, there have been the following changes: {$a->changes}';
$string['description_form'] = "Description";
$string['description_overrides'] = 'Overrides group description';
$string['description_overrides_help'] = 'This field shows the group\'s description. When you save your changes, the group\'s description will be overwritten with the content of this field.';
$string['distribution_algorithm'] = "Groupdistribution algorithm";
$string['distribution_saved'] = 'Distribution saved.';
$string['distribution_table'] = 'Distribution table';
$string['enddate'] = 'Rating ends at:';
$string['global_max_size'] = 'Sets the maximum number of students for all groups to this value.';
$string['group'] = 'Group';
$string['group_added_to_rating'] = 'Group added to rating';
$string['group_description'] = 'Description';
$string['group_description_changed'] = 'Group description changed';
$string['group_not_in_course'] = 'One of the groups does not belong to this course.';
$string['group_picture'] = 'Group\'s picture:';
$string['group_removed_from_rating'] = 'Group removed from rating';
$string['group_teachers'] = 'Group\'s teacher(s)';
$string['groupdistribution'] = 'Groupdistribution';
$string['groupdistribution:addinstance'] = 'Create a new groupdistribution';
$string['groupdistribution:give_rating'] = 'Gives ratings for groups';
$string['groupdistribution:group_teacher'] = 'Teacher responsible for a group';
$string['groupdistribution:start_distribution'] = 'Starts the distribution algorithm';
$string['groupdistribution_date_changed'] = 'Rating period changed';
$string['groupdistribution_name'] = 'Groupdistribution\'s name';
$string['invalid_dates'] = 'The begin date must be before the end date!';
$string['invalid_path'] = 'Invalid path!';
$string['max_timelimit'] = 'Groupdistribution algorithm time limit in seconds';
$string['max_timelimit_desc'] = 'This controls after how many seconds the distribution algorithm gets interrupted by PHP. If teachers need more time to compute their distributions, you can increase this value.';
$string['maxsize_form'] = 'Maximum number of students in group';
$string['maxsize_setting'] = 'Default maximum number of students per group';
$string['maxsize_setting_desc'] = 'This is the default value for the maximum number of students in the groupdistribution settings form.';
$string['modulename'] = 'Groupdistribution';
$string['modulenameplural'] = 'Groupdistributions';
$string['modulename_help'] = 'Groupdistribution allows students to give ratings to groups during a rating period. After this period is over, an algorithm will distribute the students according to their ratings into the groups.';
$string['negative_cycle'] = 'Negative cycle detected!';
$string['nogroupdistributions'] = 'This course does not contain any groupdistribution activities';
$string['no_groups_to_rate'] = 'There are no groups to rate.';
$string['no_rating_given'] = 'None';
$string['only_one_per_course'] = 'You can only have one groupdistribution per course!';
$string['pluginadministration'] = 'Groupdistribution administration';
$string['pluginname'] = 'Groupdistribution';
$string['rate_group'] = "You can change your rating";
$string['rate_group_help'] = "The better the rating for a group, the higher is your chance to be distributed in this group.";
$string['rate_group_not_saved'] = "You have not rated this group yet";
$string['rateable_form'] = 'Can students view and rate this group via Groupdistribution?';
$string['rating_bad'] = "Bad";
$string['rating_best'] = "Best";
$string['rating_good'] = "Good";
$string['rating_has_begun'] = 'The rating period has started and lasts until {$a->until}.';
$string['rating_impossible'] = "Impossible";
$string['rating_is_over'] = 'The rating period has ended. You can\'t rate the groups anymore.';
$string['rating_ok'] = "Ok";
$string['rating_worst'] = "Worst";
$string['ratings_saved'] = 'Your ratings have been saved.';
$string['ratings_table'] = 'Ratings table';
$string['set_max_size_button'] = 'Change the maximum number of students for all groups';
$string['show_names'] = 'Show students\' names in ratings table';
$string['show_names_desc'] = 'You can protect students\' privacy by hiding their names in the ratings table. This prevents teachers from knowing about a specific student\'s ratings.';
$string['show_rating_period'] = 'The rating period begins at {$a->begin} and lasts until {$a->end}';
$string['show_table'] = 'Show ratings table';
$string['start_distribution'] = "Start distribution";
$string['start_distribution_explanation'] = 'Start the distribution process. This might take some time.';
$string['too_early_to_distribute'] = 'You can start the distribution after the rating period has ended.';
$string['too_early_to_rate'] = 'You can not yet give your ratings. Please come back after the rating period has started.';
$string['other_changes'] = 'Other changes';
$string['unassigned_users'] = 'Students without a group';
$string['view_distribution_table'] = 'Shows how many students got into a group with a specific rating.';
$string['view_ratings_table'] = 'Show the ratings of all students in a table.';
$string['view_ratings_table_explanation'] = 'This table shows all ratings given by students. A rating with a border means that the student is a member in the corresponding group.';
$string['your_rating'] = 'Your rating';
