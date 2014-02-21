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
 * @package    mod
 * @subpackage mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('TO', 0);
define('WEIGHT', 1);
define('FROM', 2);

define('ACTION_RATE', 'rate');
define('ACTION_START', 'start_distribution');
define('SHOW_TABLE', 'show_table');

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/group/lib.php');

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
function distribute_users_in_course($courseid) {
    global $DB;

    // Set the time limit to prevent the algorithm from running forever
    set_time_limit(get_config('mod_groupdistribution', 'timelimit'));

    // Load data from database
    $grouprecords = get_rateable_groups_for_course($courseid);
    $ratings = all_ratings_for_rateable_groups_from_raters_in_course($courseid);
    $usercount = count(every_rater_in_course($courseid));

    $distributions = compute_distribution($grouprecords, $ratings, $usercount);

    clear_all_groups_in_course($courseid);

    foreach ($distributions as $groupsid => $users) {
        foreach ($users as $userid) {
            groups_add_member($groupsid, $userid);
        }
    }
}

function compute_distribution($grouprecords, $ratings, $usercount)
{
    $groupdata = array();
    foreach ($grouprecords as $record) {
        $groupdata[$record->groupsid] = $record;
    }
    $groupcount = count($groupdata);

    // Construct the datstructures for the algorithm

    // A directed weighted bipartite graph.
    // A source is connected to all users with unit cost.
    // The users are connected to their groups with cost equal to their rating.
    // The groups are connected to a sink with unit cost.
    $graph = array();
    // Index of source and sink in the graph
    $source = 0;
    $sink = $groupcount + $usercount + 1;
    // These tables convert userids to their index in the graph
    // The range is [1..$usercount]
    $fromuserid = array();
    $touserid = array();
    // These tables convert groupids to their index in the graph
    // The range is [$usercount + 1 .. $usercount + $groupcount]
    $fromgroupid = array();
    $togroupid = array();

    // User counter
    $ui = 1;
    // Group counter
    $gi = $usercount + 1;

    // Fill the conversion tables for group and user ids
    foreach ($ratings as $id => $rating) {
        if (!array_key_exists($rating->userid, $fromuserid)) {
            $fromuserid[$rating->userid] = $ui;
            $touserid[$ui] = $rating->userid;
            $ui++;
        }
        if (!array_key_exists($rating->groupsid, $fromgroupid)) {
            $fromgroupid[$rating->groupsid] = $gi;
            $togroupid[$gi] = $rating->groupsid;
            $gi++;
        }
    }

    // Add source, sink and number of nodes to the graph
    $graph[$source] = array();
    $graph[$sink] = array();
    $graph['count'] = $groupcount + $usercount + 2;

    // Add users and groups to the graph and connect them to the source and sink
    foreach ($fromuserid as $id => $user) {
        $graph[$user] = array();
        array_push($graph[$source], array(FROM => $source, TO => $user, WEIGHT => 0));
    }
    foreach ($fromgroupid as $id => $group) {
        $graph[$group] = array();
        array_push($graph[$group], array(FROM => $group, TO => $sink, WEIGHT => 0, 'space' => $groupdata[$id]->maxsize));
    }

    // Add the edges representing the ratings to the graph
    foreach ($ratings as $id => $rating) {
        $user = $fromuserid[$rating->userid];
        $group = $fromgroupid[$rating->groupsid];
        $weight = $rating->rating;
        if ($weight > 0) {
            array_push($graph[$user], array(FROM => $user, TO => $group, WEIGHT => $weight));
        }
    }

    // Now that the datastructure is complete, we can start the algorithm
    // This is an adaptation of the Ford-Fulkerson algorithm
    // (http://en.wikipedia.org/wiki/Ford%E2%80%93Fulkerson_algorithm)
    for ($i = 1; $i <= $usercount; $i++) {
        // Look for an augmenting path (a shortest path from the source to the sink)
        $path = find_shortest_path($source, $sink, $graph);
        // If ther is no such path, it is impossible to fit any more users into groups.
        if (is_null($path)) {
            // Stop the algorithm
            continue;
        }
        // Reverse the augmentin path, thereby distributing a user into a group
        reverse_path($path, $graph);
    }

    return extract_groupdistribution($graph, $touserid, $togroupid);
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
               WHERE g.courseid = :courseid AND d.israteable = 1
               ORDER by g.name';
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
                  ON g.id = r.groupsid AND r.userid = :userid
               WHERE g.courseid = :courseid AND d.israteable = 1
               ORDER by g.name";
    return $DB->get_records_sql($sql, array('courseid' => $courseid, 'userid' => $userid));
}

/**
 * Returns all ratings for groups in the course with id $courseid from users who can give ratings.
 */
function all_ratings_for_rateable_groups_from_raters_in_course($courseid) {
    global $DB;

    $sql = 'SELECT r.*
                FROM {groupdistribution_data} AS d
                JOIN {groupdistribution_ratings} AS r
                  ON d.groupsid = r.groupsid
                JOIN {groups} AS g
                  ON d.groupsid = g.id
               WHERE d.courseid = :courseid AND d.israteable = 1';

    $ratings = $DB->get_records_sql($sql, array('courseid' => $courseid));
    $raters = every_rater_in_course($courseid);

    // Filter out everyone who can't give ratings
    $fromraters = array_filter($ratings, function($rating) use ($raters) {
        return array_key_exists($rating->userid, $raters);
    });

    return $fromraters;
}

/**
 * Returns the groupdistribution instance id in the course with id $courseid.
 */
function get_groupdistribution_context($courseid) {
    // There is only one instance per course
    $modules = get_coursemodules_in_course('groupdistribution', $courseid);
    $module = array_pop($modules);
    $ctx = context_module::instance($module->id);
    return $ctx;
}

/**
 * Returns all users in the course with id $courseid who can give a rating.
 */
function every_rater_in_course($courseid) {
    $ctx = get_groupdistribution_context($courseid);
    $raters = get_enrolled_users($ctx, 'mod/groupdistribution:give_rating');
    return $raters;
}

/**
 * Returns all group_teachers in the course with id $courseid and
 * group with id $groupid.
 */
function every_group_teacher_in_group($courseid, $groupid) {
    global $DB;

    $ctx = get_groupdistribution_context($courseid);
    $teachers = get_enrolled_users($ctx, 'mod/groupdistribution:group_teacher', $groupid);

    return $teachers;
}

/**
 * Returns all group memberships from users who can give ratings,
 * for rateable groups in the course with id $courseid.
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
    $raters = every_rater_in_course($courseid);
    foreach ($records as $r) {

        // Ignore all members who can't give ratings
        if (!array_key_exists($r->userid, $raters)) {
            continue;
        }
        if (!array_key_exists($r->userid, $memberships)) {
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

    foreach ($memberships as $userid => $groups) {
        foreach ($groups as $groupid => $ignored) {
            groups_remove_member($groupid, $userid);
        }
    }
}

/**
 * Extracts a distribution from a graph.
 *
 * @param $graph a groupdistribution graph
 *         on which the distribution algorithm has been run
 * @param $touserid a map mapping from indexes in the graph to userids
 * @param $togroupid a map mapping from indexes in the graph to groupids
 * @return an array of the form array(groupid => array(userid, ...), ...)
 */
function extract_groupdistribution($graph, $touserid, $togroupid) {
    $distribution = array();
    foreach ($togroupid as $index => $groupid) {
        $group = $graph[$index];
        $distribution[$groupid] = array();
        foreach ($group as $assignment) {
            $user = $assignment[TO];
            if (array_key_exists($user, $touserid)) {
                $distribution[$groupid][] = $touserid[$user];
            }
        }
    }
    return $distribution;
}

function get_rating_names() {
    return array(
        'impossible',
        'worst',
        'bad',
        'ok',
        'good',
        'best');
}

/**
 * Reverses all edges along $path in $graph
 */
function reverse_path($path, &$graph) {
    if (is_null($path) or count($path) < 2) {
        print_error('invalid_path', 'groupdistribution');
    }

    // Walk along the path
    for ($i = count($path) - 1; $i > 0; $i--) {
        $from = $path[$i];
        $to = $path[$i - 1];
        $edge = null;
        $offset = -1;
        // Find the edge
        foreach ($graph[$from] as $index => &$e) {
            if ($e[TO] == $to) {
                $edge = $e;
                $offset = $index;
                break;
            }
        }
        // The second to last node in a path has to be a group node.
        // Reduce its space by one, because one user just got distributed into it.
        // If there is still space left in this group, stop here.
        if ($i == 1 and $e['space'] > 1) {
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
    for ($i = 0; $i < $count; $i++) {
        if ($i == $from) {
            $dists[$i] = 0;
        } else {
            $dists[$i] = -INF;
        }
        $preds[$i] = null;
    }

    while (!empty($edges) and $counter < $limit) {
        $counter++;

        $e = array_pop($edges);
        $f = $e[FROM];
        $t = $e[TO];
        $dist = $e[WEIGHT] + $dists[$f];

        // If this edge improves a distance update the tables and the edges stack
        if ($dist > $dists[$t]) {
            $dists[$t] = $dist;
            $preds[$t] = $f;
            foreach ($graph[$t] as $newedge) {
                $edges[] = $newedge;
            }
        }
    }

    // A valid groupdistribution graph can't contain a negative edge
    if ($counter == $limit) {
        print_error('negative_cycle', 'groupdistribution');
    }

    // If there is no path to $to, return null
    if (is_null($preds[$to])) {
        return null;
    }

    // Use the preds table to reconstruct the shortest path
    $path = array();
    $p = $to;
    while ($p != $from) {
        $path[] = $p;
        $p = $preds[$p];
    }
    $path[] = $from;

    return $path;
}

