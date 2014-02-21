moodle-mod_groupdistribution
============================
A Moodle module which simplifies the distribution of students into groups that are constrained by a maximum number of members.
A teacher can set a time period during which students can give ratings for groups. After the period an algorithm tries to distribute the students into the groups according to their ratings. This gives all students an equal chance to enter their preferred group instead of other distribution algorithms like first-come, first-served.


Requirements
============
This plugin requires Moodle 2.5.4+


Changes
=======
2014-01-31 - Initial version (Release candidate)


Installation
============
Install the plugin like any other plugin to folder
/mod/groupdistribution

See http://docs.moodle.org/25/en/Installing_plugins for details on installing Moodle plugins


Settings
========
The groupdistribution module has three settings which can be set on the plugin's settings page:

1. You can choose if the teachers can see a student's name next to his/her rating in the ratings table.

2. You can set a default maximum size for groups.

3. You can set a maximum time limit for the distribution algorithm. This feature tries to increase the standard PHP time limit of 30 seconds. Depending on your PHP settings it is possible for this setting to be ignored by PHP. This can be circumvented by changing the time limit directly in the PHP.ini file.


Usage
=====

Teachers
--------
Teachers can set the time period during which students can give their rating and the individual groups' maximum size on the activity's settings page. It is also possible to make a group invisible to the distribution algorithm and students, so that the distribution algorithm will skip it.

When a student rates the groups, he can see the groups' names, pictures and descriptions. If a group represents a periodic event (for example a weekly tutorial), its description can be used to tell the students about when and where the event will take place. The groupdistribution settings page makes it easy to change groups description.

When installing mod_groupdistribution, it creates a capability mod/groupdistribution:group_teacher. There is no default role associated with this capability. When a person with this capability is in a group, the person's name and picture will be shown alongside the group to the students during the rating period. This can be used to represent the tutor of the group.

After the rating period is over, a teacher can start the distribution process and view some additional data about the distribution. It is always possible to change maximum group sizes and redo the distribution, if the outcome was not desirable. Group memberships can of course be changed manually afterwards via the standard Moodle tools.

The algorithm is fair and will always find the solution with the fewest students without a group and the best average rating. Unfortunately, this may take some time.


Students
--------
The groupdistribution activity informs students about changes to the rating period and rateable groups. These changes are shown on the student's course overview block and the course's recent activity block.

When a student wants to rate his groups, he/she is presented with a list containing the group names, their descriptions, possibly the names of the groups' teachers, and a menu from which the student can choose a rating. The student has to give at least two ratings better than 'impossible'. A student will never be distributed into a group with the rating 'impossible'.


Themes
======
mod_groupselection should work with all themes from moodle core.


Further information
===================
Report a bug or suggest an improvement: https://github.com/moodleuulm/moodle-mod_groupselection/issues


Moodle release support
======================
Due to limited ressources, mod_groupselection is only maintained for the most recent major release of Moodle. However, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

There may be several weeks after a new major release of Moodle has been published until I can do a compatibility check and fix problems if necessary. If you encounter problems with a new major release of Moodle - or can confirm that mod_groupselection still works with a new major relase - please let me know on https://github.com/moodleuulm/moodle-mod_groupselection/issues


Right-to-left support
=====================
This plugin has not been tested with Moodle's support for right-to-left (RTL) languages.
If you want to use this plugin with a RTL language and it doesn't work as-is, you are free to send me a pull request on
github with modifications.


Algorithm
=========
This module uses a modified Ford-Fulkerson algorithm to solve the so called minimum-cost flow problem. To find augmenting paths a modified Bellman-Ford algorithm is used.

The algorithm has a worst case time complexity of O(n^4) where n is the number of students. That means it might take some time to find a solution. Some experience values:
30 seconds for 100 students
2 minutes for 400 students


Copyright
=========
Written by Stefan Koegel, University of Ulm
Packaged and maintained by Alexander Bias, University of Ulm
