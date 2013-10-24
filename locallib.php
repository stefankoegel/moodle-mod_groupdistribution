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
 * Internal library of functions for module groupdistribution.
 *
 * Contains the algorithm for the group distribution and some helper functions
 * that wrap useful SQL querys.
 *
 * @package    mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('TO', 0);
define('WEIGHT', 1);
define('FROM', 2);

define('ACTION_RATE', 'rate');
define('ACTION_START', 'start_distribution');
define('ACTION_CLEAR', 'clear_groups');
define('SHOW_TABLE', 'show_table');

require_once($CFG->dirroot . '/mod/groupdistribution/lib.php');
require_once($CFG->dirroot . '/group/lib.php');

/**
 * Starts the distribution algorithm.
 * Uses the users' ratings and a minimum-cost maximum-flow algorithm
 * to distribute the users fairly into the groups.
 * (see http://en.wikipedia.org/wiki/Minimum-cost_flow_problem)
 * After the algorithm is done, users are removed from their current
 * groups (see clear_all_groups_in_course()) and redistributed
 * according to the computed distriution.
 *
 * @param $courseid id of the course in which to distribute the users
 * @param $timeout maximum time in seconds after which the algorithm gets stopped
 */
function distribute_users_in_course($courseid, $timeout=30) {
	global $DB;

	// Set the time limit to prevent the algorithm from running forever
	if($timeout < 0) {
		$timeout = 30;
	}
	if($timeout > 600) {
		$timeout = 600;
	}
	set_time_limit($timeout);

	// Load data from database
	$groupRecords = get_rateable_groups_for_course($courseid);

	$groupData = array();
	foreach($groupRecords as $record) {
		$groupData[$record->groupsid] = $record;
	}

	$groupCount = count($groupData);

	$userCount = count(all_enrolled_users_in_course($courseid));

	$ratings = get_all_ratings_for_rateable_groups_in_course($courseid);

	// Construct the datstructures for the algorithm

	// A directed weighted bipartite graph.
	// A source is connected to all users with unit cost.
	// The users are connected to their groups with cost equal to their rating.
	// The groups are connected to a sink with unit cost.
	$graph = array();
	// Index of source and sink in the graph
	$source = 0;
	$sink = $groupCount + $userCount + 1;
	// These tables convert userids to their index in the graph
	// The range is [1..$userCount]
	$fromUserid = array();
	$toUserid = array();
	// These tables convert groupids to their index in the graph
	// The range is [$userCount + 1 .. $userCount + $groupCount]
	$fromGroupid = array();
	$toGroupid = array();

	// User counter
	$ui = 1;
	// Group counter
	$gi = $userCount + 1;

	// Fill the conversion tables for group and user ids
	foreach($ratings as $id => $rating) {
		if(!array_key_exists($rating->userid, $fromUserid)) {
			$fromUserid[$rating->userid] = $ui;
			$toUserid[$ui] = $rating->userid;
			$ui++;
		}
		if(!array_key_exists($rating->groupsid, $fromGroupid)) {
			$fromGroupid[$rating->groupsid] = $gi;
			$toGroupid[$gi] = $rating->groupsid;
			$gi++;
		}
	}

	// Add source, sink and number of nodes to the graph
	$graph[$source] = array();
	$graph[$sink] = array();
	$graph['count'] = $groupCount + $userCount + 2;

	// Add users and groups to the graph and connect them to the source and sink
	foreach($fromUserid as $id => $user) {
		$graph[$user] = array();
		array_push($graph[$source], array(FROM => $source, TO => $user, WEIGHT => 0));
	}
	foreach($fromGroupid as $id => $group) {
		$graph[$group] = array();
		array_push($graph[$group], array(FROM => $group, TO => $sink, WEIGHT => 0, 'space' => $groupData[$id]->maxsize));
	}

	// Add the edges representing the ratings to the graph
	foreach($ratings as $id => $rating) {
		$user = $fromUserid[$rating->userid];
		$group = $fromGroupid[$rating->groupsid];
		$weight = $rating->rating;
		if($weight > 0) {
			array_push($graph[$user], array(FROM => $user, TO => $group, WEIGHT => $weight));
		}
	}

	// Now that the datastructure is complete, we can start the algorithm
	// This is an adaptation of the Ford-Fulkerson algorithm
	// (http://en.wikipedia.org/wiki/Ford%E2%80%93Fulkerson_algorithm)
	for($i = 1; $i <= $userCount; $i++) {
		// Look for an augmenting path (a shortest path from the source to the sink)
		$path = find_shortest_path($source, $sink, $graph);
		// If ther is no such path, it is impossible to fit any more users into groups.
		if(is_null($path)) {
			// Stop the algorithm
			continue;
		}
		// Reverse the augmentin path, thereby distributing a user into a group
		reverse_path($path, $graph);
	}

	$distributions = extract_groupdistribution($graph, $toUserid, $toGroupid);

	clear_all_groups_in_course($courseid);

	foreach($distributions as $groupsid => $users) {
		foreach($users as $userid) {
			groups_add_member($groupsid, $userid);
		}
	}
}

/**
 * Returns all groups in the course with id $courseid that are rateable.
 */
function get_rateable_groups_for_course($courseid) {
	global $DB;

	$sql = 'SELECT *
		        FROM {groupdistribution_data} AS d
		        JOIN {groups} AS g
		          ON g.id = d.groupsid
		       WHERE g.courseid = :courseid AND d.israteable = 1';
	return $DB->get_records_sql($sql, array('courseid' => $courseid));
}

/**
 * Returns all ratings from the user with id $userid for groups
 * in the course with id $courseid.
 */
function get_rating_data_for_user_in_course($courseid, $userid) {
	global $DB;

	$sql = "SELECT d.id, g.description, g.name, d.groupsid, g.courseid, r.rating, r.id AS ratingid
		        FROM {groupdistribution_data} AS d
		        JOIN {groups} AS g
		          ON g.id = d.groupsid
		   LEFT JOIN {groupdistribution_ratings} AS r
		          ON g.id = r.groupsid
		       WHERE g.courseid = :courseid AND d.israteable = 1 AND (r.userid = :userid OR r.userid IS NULL)";
	return $DB->get_records_sql($sql, array('courseid' => $courseid, 'userid' => $userid));
}

/**
 * Returns all ratings for groups in the course with id $courseid.
 */
function get_all_ratings_for_rateable_groups_in_course($courseid) {
	global $DB;

	$sql = 'SELECT r.*
		        FROM {groupdistribution_data} AS d
		        JOIN {groupdistribution_ratings} AS r
		          ON d.groupsid = r.groupsid
		       WHERE d.courseid = :courseid AND d.israteable = 1';
	return $DB->get_records_sql($sql, array('courseid' => $courseid));
}

/**
 * Returns all users in the course with id $courseid.
 */
function all_enrolled_users_in_course($courseid) {
	global $DB;
	
	$context = get_context_instance(CONTEXT_COURSE, $courseid);
	$student_role = $DB->get_record('role', array('shortname' => 'student'));
	// Documentation: lib/acceslib.php
	$students = get_role_users($student_role->id, $context, false, 'u.id, u.username');
	return $students;
}

/**
 * Returns all group memberships for rateable groups in the course with id $courseid.
 * Also contains the rating the user gave for that group or null if he gave none.
 *
 * @return array of the form array($userid => array($groupid => $rating, ...), ...)
 *         i.e. for every user who is a member of at least one rateable group,
 *         the array contains a set of ids representing the groups the user is a member of
 *         and possibly the respective rating.
 */
function memberships_per_course($courseid) {
	global $DB;

	$query = 'SELECT gm.id, gm.userid, gm.groupid, r.rating
	            FROM {groups_members} AS gm
	            JOIN {groups} AS g
	              ON gm.groupid = g.id
	            JOIN {groupdistribution_data} as d
	              ON gm.groupid = d.groupsid
	       LEFT JOIN {groupdistribution_ratings} as r
	              ON gm.groupid = r.groupsid AND gm.userid = r.userid
	           WHERE g.courseid = :courseid AND d.israteable = 1';
	$records = $DB->get_records_sql($query, array('courseid' => $courseid));
	$memberships = array();
	foreach($records as $r) {
		if(!array_key_exists($r->userid, $memberships)) {
			$memberships[$r->userid] = array();
		}
		$memberships[$r->userid][$r->groupid] = $r->rating;
	}
	return $memberships;
}

/**
 * Removes all members from rateable groups in the curse with id $courseid.
 */
function clear_all_groups_in_course($courseid) {
	$memberships = memberships_per_course($courseid);

	foreach($memberships as $userid => $groups) {
		foreach($groups as $groupid => $_) {
			groups_remove_member($groupid, $userid);
		}
	}
}

/**
 * Extracts a distribution from a graph.
 * 
 * @param $graph a groupdistribution graph
 *         on which the distribution algorithm has been run
 * @param $toUserid a map mapping from indexes in the graph to userids
 * @param $toGroupid a map mapping from indexes in the graph to groupids
 * @return an array of the form array(groupid => array(userid, ...), ...)
 */
function extract_groupdistribution($graph, $toUserid, $toGroupid) {
	$distribution = array();
	foreach($toGroupid as $index => $groupid) {
		$group = $graph[$index];
		$distribution[$groupid] = array();
		foreach($group as $assignment) {
			$user = $assignment[TO];
			if(array_key_exists($user, $toUserid)) {
				$distribution[$groupid][] = $toUserid[$user];
			}
		}
	}
	return $distribution;
}

/**
 * Reverses all edges along $path in $graph
 */
function reverse_path($path, &$graph) {
	if(is_null($path) or count($path) < 2) {
		print_error('invalid_path', 'groupdistribution');
	}

	// Walk along the path
	for($i = count($path) - 1; $i > 0; $i--) {
		$from = $path[$i];
		$to = $path[$i - 1];
		$edge = NULL;
		$offset = -1;
		// Find the edge
		foreach($graph[$from] as $index => &$e) {
			if($e[TO] == $to) {
				$edge = $e;
				$offset = $index;
				break;
			}
		}
		// The second to last node in a path has to be a group node.
		// Reduce its space by one, because one user just got distributed into it.
		// If there is still space left in this group, stop here.
		if($i == 1 and $e['space'] > 1) {
			$e['space']--;
			continue;
		}
		// Remove the edge
		array_splice($graph[$from], $offset, 1);
		// Add a new edge in the opposite direction whose weight has an opposite sign
		array_push($graph[$to], array(FROM => $to, TO => $from, WEIGHT => -$edge[WEIGHT]));
	}
}

/**
 * Uses a modified Bellman-Ford algorithm to find a shortest path
 * from $from to $to in $graph. We can't use Dijkstra here, because
 * the graph contains edges with negative weight.
 *
 * @param $from index of starting node
 * @param $to index of end node
 * @param $graph the graph in which to find the path
 * @return array with the of the nodes in the path 
 */
function find_shortest_path($from, $to, &$graph) {
	// Table of distances known so far
	$dists = array();
	// Table of predecessors (used to reconstruct the shortest path later)
	$preds = array();
	// Stack of the edges we need to test next
	$edges = $graph[$from];
	// Number of nodes in the graph
	$count = $graph['count'];

	// To prevent the algorithm from getting stuck in a loop with
	// with negative weight, we stop it after $count ^ 3 iterations
	$counter = 0;
	$limit = $count * $count * $count;

	// Initialize dists and preds
	for($i = 0; $i < $count; $i++) {
		if($i == $from) {
			$dists[$i] = 0;
		} else {
			$dists[$i] = -INF;
		}
		$preds[$i] = NULL;
	}

	while(!empty($edges) and $counter < $limit) {
		$counter++;

		$e = array_pop($edges);
		$f = $e[FROM];
		$t = $e[TO];
		$dist = $e[WEIGHT] + $dists[$f];

		// If this edge improves a distance update the tables and the edges stack
		if($dist > $dists[$t]) {
			$dists[$t] = $dist;
			$preds[$t] = $f;
			foreach($graph[$t] as $new_edge) {
				$edges[] = $new_edge;
			}	
		}
	}

	// A valid groupdistribution graph can't contain a negative edge
	if($counter == $limit) {
		print_error('negative_cycle', 'groupdistribution');
	}

	// If there is no path to $to, return null
	if(is_null($preds[$to])) {
		return NULL;
	}

	// Use the preds table to reconstruct the shortest path
	$path = array();
	$p = $to;
	while($p != $from) {
		$path[] = $p;
		$p = $preds[$p];
	}
	$path[] = $from;

	return $path;
}

