Groupdistribution
=================

This module simplifies the distribution of users into groups that are
constrained by a maximum number of members.
A teacher can set a time period during which users can give ratings for
groups. After the period an algorithm tries to distribute the users fairly
into the groups according to their rating. This gives all users an equal
chance to enter their preferred group instead of favoring those
who come first.

There can be only one module per course.


Teachers
========

Teachers can set the time period during which users can give their rating.
They can also set maximum sizes for every group. It is also possible to
make a group invisible to the distribution algorithm and users, so that
this module leaves it alone.

After the rating period is over a teacher can start the distribution
process and view some additional data about the distribution. It is always
possible to change maximum group sizes and redo the distribution, if the
outcome was not desirable. Group memberships can of course be changed
manually via the standard Moodle tools.

The algorithm is fair an will always find the solution with the fewest users
without a group and the best average rating. Unfortunately, this may take
some time.


Users
=====

When a user wants to rate his groups, he is presented with a list containing
the group names, their descriptions and a menu from which he can choose a rating.
The user has to give at least two ratings better than 'impossible', because
the algorithm will not distribute the user into a group with this rating.


Algorithm
=========

This module uses a modified Ford-Fulkerson algorithm to solve the so called
minimum-cost flow problem. To find augmenting paths a modified Bellman-Ford
algorithm is used.

The algorithm has a worst case time complexity of O(n^4) where n is the
number of users. That means it might take VERY LONG to find a solution.
Some experience values:
30 seconds for 100 users
2 minutes for 400 users


Usage
=====

Put the module files into Moodles mod/ directory and install it via the
administration page.

After the installation you can add the Groupdistribution activity to a course
and configure it.
