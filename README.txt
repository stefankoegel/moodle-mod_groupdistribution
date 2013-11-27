Groupdistribution
=================

Summary
-------

This module simplifies the distribution of students into groups that are
constrained by a maximum number of members.
A teacher can set a time period during which students can give ratings for
groups. After the period an algorithm tries to distribute the students
into the groups according to their ratings. This gives all students an equal
chance to enter their preferred group instead of favouring those
who come first.


Teachers
--------

Teachers can set the time period during which students can give their rating
and the individual groups' maximum size on the activity's settings page.
It is also possible to make a group invisible to the distribution algorithm
and students, so that the distribution algorithm leaves it alone.

When a student rates the groups he can see the groups' names, pictures and
descriptions. If a group represents a weekly tutorial, its description can
be used to tell the students about when and where the tutorial will be held.
The groupdistribution settings page makes it easy to change groups description.

The activity defines a capability called group_teacher. There is no default
role associated with this capability.
When a person with this capability is added to group, the persons name and
picture will be shown alongside the group.
This can be used to represent the tutor of the group.

After the rating period is over a teacher can start the distribution
process and view some additional data about the distribution. It is always
possible to change maximum group sizes and redo the distribution, if the
outcome was not desirable. Group memberships can of course be changed
manually via the standard Moodle tools.

The algorithm is fair an will always find the solution with the fewest students
without a group and the best average rating. Unfortunately, this may take
some time.


Students
--------

The groupdistribution activity informs students about changes to the rating
period and rateable groups. These changes are shown on the student's course
overview page and the course's recent activity block.

When a student wants to rate his groups, he is presented with a list containing
the group names, their descriptions, possibly the names of the groups' teachers,
and a menu from which the student can choose a rating.
The student has to give at least two ratings better than 'impossible'.
A student will never be distributed into a group with the rating 'impossible'.


Administrator
-------------

The groupdistribution module has three settings:
- You can choose if the teachers can see student's name next to his/her rating
  in the ratings table.
- You can set a standard maximum size for all groups.
- You can set a maximum time limit for the distribution algorithm.
  This feature tries to increase the standard php time limit of 30 seconds.
  Depending on your php settings it is possible for this setting to be
  ignored by php. This can be circumvented by changing the time limit directly
  in the php.ini file.


Algorithm
---------

This module uses a modified Ford-Fulkerson algorithm to solve the so called
minimum-cost flow problem. To find augmenting paths a modified Bellman-Ford
algorithm is used.

The algorithm has a worst case time complexity of O(n^4) where n is the
number of students. That means it might take VERY LONG to find a solution.
Some experience values:
30 seconds for 100 students
2 minutes for 400 students


Installation
------------

Put the module files into Moodles mod/ directory and install it via the
administration page.

After the installation you can add the Groupdistribution activity to a course
and configure it.
