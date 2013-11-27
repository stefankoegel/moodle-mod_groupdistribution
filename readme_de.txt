Groupdistribution
=================

Zusammenfassung
---------------

Dieses Plugin die Verteilung von Studenten auf Gruppen in denen nur eine
begrenzte Anzahl an Studenten platz findet.
Ein Lehrer kann eine Bewertungsperiode einstellen, während der Studenten
Bewertungen für Gruppen abgeben können. Nach Ablauf dieser Periode versucht
ein Algorithmus die Studenten, entsprechend ihrer Bewertungen,
auf die Gruppen zu verteilen. Dies gibt allen Studenten die gleiche Chance
auf ihre bevorzugte Gruppe verteilt zu werden, anstatt diejenigen zu
bevorzugen, die zufällig als erste da waren.

Lehrer
------

Lehrer können den Zeitraum während dem Studenten ihre Bewertungen abgeben
können sowie die maximale Größe der einzelnen Gruppen auf der
Einstellungsseite der Groupdistribution festlegen. Es ist auch möglich
Gruppen vor dem Verteilungsalgorithmus und den Studenten zu verstecken,
so dass sie von Groupdistribution nicht verändert werden.

Wenn ein Student eine Gruppe bewertet kann er ihren Namen, Bild und
Beschreibung sehen. Wenn eine Gruppe ein wöchentliches Tutorium repräsentiert,
kann ihre Beschreibung benutzt werden um Ort und Zeit des Tutoriums anzugeben.
Die Einstellungsseite der Groupdistribution ermöglicht auch das ändern der
Gruppenbeschreibung.

Groupdistribution definiert außerdem eine "Capability" namens "group_teacher".
Standardmäßig ist keine Rolle mit dieser "Capability" assoziiert. Wenn eine
Person in einer Gruppe diese "Capability" besitzt, wird ihr Name und Bild
zusätzlich zur Beschreibung während der Bewertung durch die Studenten
angezeigt. Dadurch kann zum Beispiel der Tutor eines Tutoriums dargestellt
werden.

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
