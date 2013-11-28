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

Nach Ablauf der Bewertungsperiode kann ein Lehrer den Verteilungsalgorithmus
starten und sich zusätzliche Informationen über die Verteilung ansehen.
Es ist jederzeit möglich die maximale Gruppengröße zu verändern und den
Verteilungsalgorithmus erneut zu starten, falls das Ergebnis nicht den
Erwartungen entspricht. Gruppenmitgliedschaften können natürlich auch von
Hand über die normalen Moodle Werkzeuge bearbeitet werden.

Der Algorithmus ist fair und versucht die Verteilung zu finden bei der
die meisten Studenten in einer Gruppe sind und die Durchschnittsbewertung
möglichst gut ist. Dies kann allerdings einige Zeit dauern.


Studenten
---------

Die Groupdistribution informiert Studenten über Änderungen an der
Bewertungsperiode und über das Hinzufügen oder Entfernen von Gruppen. Diese
Informationen werden auf der Kursübersichtsseite des Studenten und auf der
Kursseite angezeigt.

Wenn ein Student seine Gruppen bewertet, werden ihm die Namen, Beschreibungen
und, falls vorhanden, die Namen der Lehrer die der Gruppe zugeteilt sind
angezeigt. Er kann dann aus Dropdownmenüs Bewertungen für die Gruppen
auswählen, muss aber mindestens zwei Bewertungen besser als 'unmöglich' abgeben.
Der Algorithmus verteilt keine Studenten auf Gruppen mit der
Bewertung 'unmöglich'

Administrator
-------------

Die Groupdistribution hat drei Einstellungen:
- Man kann auswählen ob die Lehrer die Namen der Studenten neben ihren
  Bewertungen in der Bewertungstabelle sehen können.
- Man kann den Standardwert des 'Maximle Anzahl von Studenten pro Gruppe'
  Feldes festlegen
- Man kann das Zeitlimit des Verteilungsalgorithmus erhöhen.
  Diese Einstellung versucht das Zeitlimit von PHP, das normalerweise bei
  30 Sekunden liegt, zu erhöhen. Abhängig von den Einstellungen von PHP,
  kann dies scheitern. In diesem Fall muss das Zeitlimit in der php.ini Datei
  erhöht werden.


Algorithmus
-----------

Der Verteilungsalgorithmus verwendet einen modifizierten Ford-Fulkerson
Algorithmus um das sogenannte 'minimum-cost flow' Problem zu Lösen.
Er findet Erweiterungspfade mithilfe eines modifizierten Bellman-Ford
Algorithmus.

Die Laufzeitkomplexität beträgt O(n^4), wobei n die Anzahl der Studenten ist.
Das bedeutet, dass es lange dauern kann, bis die Verteilung gefunden wird.
Einige Erfahrungswerte:
30 Sekunden für 100 Studenten
2 Minuten für 400 Studenten


Installation
------------

Lege die Dateien von Groupdistribution in das mod/ Verzeichnis von Moodle
und installiere es über die Administrator Seite, danach kann man einem Kurs
die Groupdistribution Aktivität hinzufügen.
