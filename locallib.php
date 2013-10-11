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
 * Internal library of functions for module groupdistribution
 *
 * All the groupdistribution specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
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

function test_shortest_path($courseid, $timeout=30) {
	global $DB;

	if($timeout < 0) {
		$timeout = 30;
	}
	if($timeout > 600) {
		$timeout = 600;
	}
	set_time_limit($timeout);

	clear_all_groups_in_course($courseid);

	$groupRecords = get_rateable_groups_for_course($courseid);

	$groupData = array();
	foreach($groupRecords as $record) {
		$groupData[$record->groupsid] = $record;
	}

	$groupCount = count($groupData);

	$userCount = count(all_enrolled_users_in_course($courseid));

	$ratings = get_all_ratings_for_rateable_groups_in_course($courseid);

	$graph2 = array();
	$fromUserid = array();
	$toUserid = array();
	$fromGroupid = array();
	$toGroupid = array();
	$ui = 1;
	$gi = $userCount + 1;
	$source = 0;
	$sink = $groupCount + $userCount + 1;

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

	$graph2[$source] = array();
	foreach($fromUserid as $id => $user) {
		array_push($graph2[$source], array(FROM => $source, TO => $user, WEIGHT => 0));
	}
	foreach($ratings as $id => $rating) {
		$user = $fromUserid[$rating->userid];
		if(!array_key_exists($user, $graph2)) {
			$graph2[$user] = array();
		}
		$group = $fromGroupid[$rating->groupsid];
		$weight = $rating->rating;
		if($weight > 0) {
			array_push($graph2[$user], array(FROM => $user, TO => $group, WEIGHT => $weight));
		}
	}
	foreach($fromGroupid as $id => $group) {
		$graph2[$group] = array();
		array_push($graph2[$group], array(FROM => $group, TO => $sink, WEIGHT => 0, 'space' => $groupData[$id]->maxsize));
	}
	$graph2[$sink] = array();

	for($i = 1; $i <= $userCount; $i++) {
		$path = find_shortest_path($source, $sink, $sink + 1, $graph2);
		if(is_null($path)) {
			continue;
		}
		reverse_path($path, $graph2);
	}

	$distributions = extract_groupdistribution($graph2, $toUserid, $toGroupid);

	foreach($distributions as $groupsid => $users) {
		foreach($users as $userid) {
			groups_add_member($groupsid, $userid);
		}
	}
}

function get_rateable_groups_for_course($courseid) {
	global $DB;

	$sql = 'SELECT *
		        FROM {groupdistribution_data} AS d
		        JOIN {groups} AS g
		          ON g.id = d.groupsid
		       WHERE g.courseid = :courseid AND d.israteable = 1';
	return $DB->get_records_sql($sql, array('courseid' => $courseid));
}

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

function get_all_ratings_for_rateable_groups_in_course($courseid) {
	global $DB;

	$sql = 'SELECT r.*
		        FROM {groupdistribution_data} AS d
		        JOIN {groupdistribution_ratings} AS r
		          ON d.groupsid = r.groupsid
		       WHERE d.courseid = :courseid AND d.israteable';
	return $DB->get_records_sql($sql, array('courseid' => $courseid));
}

function all_enrolled_users_in_course($courseid) {
	global $DB;
	
	$context = get_context_instance(CONTEXT_COURSE, $courseid);
	$student_role = $DB->get_record('role', array('shortname' => 'student'));
	// Documentation: lib/acceslib.php
	$students = get_role_users($student_role->id, $context, false, 'u.id, u.username');
	return $students;
}

function memberships_per_course($courseid) {
	global $DB;

	$query = 'SELECT gm.id, gm.groupid, gm.userid
	            FROM {groups_members} AS gm
	            JOIN {groups} AS g
	              ON gm.groupid = g.id
	           WHERE g.courseid = :courseid';
	$records = $DB->get_records_sql($query, array('courseid' => $courseid));
	$memberships = array();
	foreach($records as $r) {
		if(!array_key_exists($r->userid, $memberships)) {
			$memberships[$r->userid] = array();
		}
		$memberships[$r->userid][$r->groupid] = true;
	}
	return $memberships;
}

function clear_all_groups_in_course($courseid) {
	$memberships = memberships_per_course($courseid);

	foreach($memberships as $userid => $groups) {
		foreach($groups as $groupid => $_) {
			groups_remove_member($groupid, $userid);
		}
	}
}

function extract_groupdistribution($graph, $toUserid, $toGroupid) {
	$distribution = array();
	foreach($toGroupid as $g => $id) {
		$group = $graph[$g];
		$distribution[$id] = array();
		foreach($group as $assignment) {
			$user = $assignment[TO];
			if(array_key_exists($user, $toUserid)) {
				$distribution[$id][] = $toUserid[$user];
			}
		}
	}
	return $distribution;
}

function pretty_print($graph, $toUserid, $toGroupid) {
	$assignments = array();
	$rating = 0;
	$users = 0;
	foreach($toGroupid as $g => $id) {
		$group = $graph[$g];
		$assignments[$id] = array();
		$inGroup = 0;
		foreach($group as $assign) {
			//TODO testen dass es nicht auf sink zeigt
			$assignments[$id][$toUserid[$assign[TO]]] = -$assign[WEIGHT];
			unset($toUserid[$assign[TO]]);
			$rating -= $assign[WEIGHT];	
			$users++;
			$inGroup++;
		}
		$assignments[$id]['assigned'] = $inGroup;
	}
	$assignments['unassigned'] = $toUserid;
	$assignments['mean'] = $rating / $users;
	//TODO die letzten die sich anmelden werden nicht verteilt
	return $assignments;
}

function reverse_path($path, &$graph) {
	if(is_null($path) or count($path) < 2) {
		print_error('Invalid path!');
	}

	for($i = count($path) - 1; $i > 0; $i--) {
		$from = $path[$i];
		$to = $path[$i - 1];
		$edge = NULL;
		$offset = -1;
		foreach($graph[$from] as $index => &$e) {
			if($e[TO] == $to) {
				$edge = $e;
				$offset = $index;
				break;
			}
		}
		if($i == 1 and $e['space'] > 1) {
			$e['space']--;
			continue;
		}
		array_splice($graph[$from], $offset, 1);
		array_push($graph[$to], array(FROM => $to, TO => $from, WEIGHT => -$edge[WEIGHT]));
	}
}

function find_shortest_path($from, $to, $vertices, &$graph) {
	$dists = array();
	$preds = array();
	$edges = $graph[$from];

	$counter = 0;
	$limit = $vertices * $vertices * $vertices;

	for($i = 0; $i < $vertices; $i++) {
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

		if($dist > $dists[$t]) {
			$dists[$t] = $dist;
			$preds[$t] = $f;
			foreach($graph[$t] as $new_edge) {
				$edges[] = $new_edge;
			}	
		}
	}

	if($counter == $limit) {
		print_error('Negative cycle detected!');
	}

	if(is_null($preds[$to])) {
		return NULL;
	}

	$path = array();
	$p = $to;
	while($p != $from) {
		$path[] = $p;
		$p = $preds[$p];
	}
	$path[] = $from;

	return $path;
}

