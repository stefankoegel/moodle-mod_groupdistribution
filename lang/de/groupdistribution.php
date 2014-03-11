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
 * German strings for groupdistribution
 *
 * @package    mod
 * @subpackage mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['at_least_two'] = 'Bitte gib mindestens zwei Bewertungen ab, die besser sind als "unmöglich".';
$string['at_least_two_groups'] = 'Ein Kurs muss mindestens zwei Gruppen enthalten damit die Gruppenverteilung funktionieren kann.<br />Sie werden weitergeleitet zur Gruppeneinstellungsseite, wo Sie Gruppen anlegen können.';
$string['at_least_two_rateable_groups'] = 'Ein Kurs muss mindestens zwei bewertbare Gruppen enthalten.';
$string['begindate'] = 'Bewertung beginnt am:';
$string['changes'] = 'Seit {$a->time}, gab es folgende Änderungen: {$a->changes}';
$string['changes_short'] = 'Es gab folgende Änderungen: {$a->changes}';
$string['description_form'] = "Beschreibung";
$string['description_overrides'] = 'Überschreibt die Beschreibung der Gruppe';
$string['description_overrides_help'] = 'Dieses Feld zeigt die Beschreibung der Gruppe. Wenn Sie die Beschreibung hier ändern, wird auch die Beschreibung der Gruppe in den Kurseinstellungen überschrieben.';
$string['distribution_algorithm'] = "Gruppenverteilungs-Algorithmus";
$string['distribution_saved'] = 'Verteilung gespeichert.';
$string['distribution_table'] = 'Gruppenverteilungs-Tabelle';
$string['enddate'] = 'Bewertung endet am:';
$string['global_max_size'] = 'Setzt die maximale Anzahl an Studenten jeder Gruppe auf diesen Wert.';
$string['group_added_to_rating'] = 'Gruppe zur Gruppenverteilung hinzugefügt';
$string['group_description'] = 'Beschreibung';
$string['group_description_changed'] = 'Beschreibung einer Gruppe geändert';
$string['group_not_in_course'] = 'Eine der Gruppen gehört nicht zum Kurs.';
$string['group_picture'] = 'Bild der Gruppe:';
$string['group_removed_from_rating'] = 'Gruppe von der Bewertung entfernt';
$string['group_teachers'] = 'Trainer der Gruppe';
$string['groupdistribution'] = 'Gruppenverteilung';
$string['groupdistribution:addinstance'] = 'Erstellt eine neue Gruppenverteilung';
$string['groupdistribution:give_rating'] = 'Gibt Bewertungen für Gruppen ab';
$string['groupdistribution:group_teacher'] = 'Trainer der für eine Gruppe zuständig ist';
$string['groupdistribution:start_distribution'] = 'Startet den Verteilungsalgorithmus';
$string['groupdistribution_date_changed'] = 'Bewertungsperiode hat sich geändert';
$string['groupdistribution_name'] = 'Name der Gruppenverteilung';
$string['invalid_dates'] = 'Das Anfangsdatum muss vor dem Enddatum sein!';
$string['invalid_path'] = 'Unerlaubter Pfad!';
$string['max_timelimit'] = 'Zeitlimit des Gruppenverteilungs-Algorithmus in Sekunden';
$string['max_timelimit_desc'] = 'Dies gibt an, nach wie vielen Sekunden der Verteilungsalgorithmus von PHP unterbrochen wird. Wenn Trainer mehr Zeit brauchen, um ihre Verteilungen zu berechnen, können Sie diesen Wert erhöhen.';
$string['maxsize_form'] = 'Maximale Anzahl an Studenten in einer Gruppe';
$string['maxsize_setting'] = 'Standardwert für die maximale Anzahl an Studenten in einer Gruppe';
$string['maxsize_setting_desc'] = 'Dies ist der Standardwert für die maximale Anzahl an Studenten pro Gruppe auf der Einstellungsseite der Gruppenverteilung.';
$string['modulename'] = 'Gruppenverteilung';
$string['modulenameplural'] = 'Gruppenverteilungen';
$string['modulename_help'] = 'Die Gruppenverteilung lässt Studenten, während einer Bewertungsperiode, Bewertungen für Gruppen angeben. Nach dieser Periode verteilt ein Algorithmus die Studenten auf die Gruppen gemäß ihren Bewertungen.';
$string['negative_cycle'] = 'Negativer Zyklus entdeckt!';
$string['nogroupdistributions'] = 'Dieser Kurs enthält keine Gruppenverteilungs-Aktivitäten';
$string['no_groups_to_rate'] = 'Es gibt leider keine Gruppen, für die Sie eine Bewertung abgeben könnten.';
$string['no_rating_given'] = 'Nichts';
$string['only_one_per_course'] = 'Es kann nur eine Gruppenverteilung pro Kurs geben!';
$string['pluginadministration'] = 'Gruppenverteilungs-Administration';
$string['pluginname'] = 'Gruppenverteilung';
$string['rate_group'] = "Sie können Ihre Bewertung ändern";
$string['rate_group_help'] = "Je besser die Bewertung, desto höher ist Ihre Chance, in die Gruppe zu kommen.";
$string['rate_group_not_saved'] = "Bitte bewerten Sie, wie gern Sie teilnehmen möchten";
$string['rateable_form'] = 'Können Studenten diese Gruppe in der Gruppenverteilung sehen und bewerten?';
$string['rating_0'] = 'Ich kann nicht teilnehmen (0)';
$string['rating_1'] = 'Ich möchte nicht teilnehmen, wenn es sich vermeiden lässt (1)';
$string['rating_2'] = 'Ich würde lieber nicht teilnehmen (2)';
$string['rating_3'] = 'Ich kann teilnehmen (3)';
$string['rating_4'] = 'Ich würde gerne teilnehmen (4)';
$string['rating_5'] = 'Ich möchte unbedingt teilnehmen (5)';
$string['rating_has_begun'] = 'Die Bewertungsperiode hat begonnen und dauert bis: {$a->until}.';
$string['rating_is_over'] = 'Die Bewertungsperiode ist vorbei. Sie können Ihre Bewertung nicht mehr abgeben.';
$string['rating_short_0'] = '0';
$string['rating_short_1'] = '1';
$string['rating_short_2'] = '2';
$string['rating_short_3'] = '3';
$string['rating_short_4'] = '4';
$string['rating_short_5'] = '5';
$string['ratings_saved'] = 'Ihre Bewertungen wurden gespeichert.';
$string['ratings_table'] = 'Bewertungs-Tabelle';
$string['set_max_size_button'] = 'Ändere die maximale Anzahl an Studenten für alle Gruppen';
$string['show_names'] = 'Zeige die Namen der Studenten in der Bewertungstabelle';
$string['show_names_desc'] = 'Sie können die Privatsphäre der Studenten schützen, indem Sie ihre Namen in der Bewertungstabelle nicht anzeigen lassen. Dies verhindert, dass Trainer erfahren, welche Bewertungen von welchem Studenten stammen.';
$string['show_rating_period'] = 'Die Bewertungsperiode beginnt am {$a->begin} und endet am {$a->end}';
$string['show_table'] = 'Zeige die Bewertungstabelle';
$string['start_distribution'] = "Starte den Verteilungsalgorithmus";
$string['start_distribution_explanation'] = 'Startet den Verteilungsalgorithmus. Dies kann etwas dauern.';
$string['too_early_to_distribute'] = 'Sie können den Verteilungsalgorithmus starten, wenn die Bewertungsperiode vorbei ist.';
$string['too_early_to_rate'] = 'Sie können Ihre Bewertung leider noch nicht abgeben. Bitte kommen Sie wieder, sobald die Bewertungsperiode begonnen hat.';
$string['other_changes'] = 'Andere Änderungen';
$string['unassigned_users'] = 'Studenten ohne Gruppe';
$string['view_distribution_table'] = 'Zeigt wie viele Studenten in eine Gruppe mit einer bestimmten Bewertung gekommen sind. Hohe Bewertungen sind besser.';
$string['view_ratings_table'] = 'Zeigt die abgegebenen Bewertungen aller Studenten in einer Tabelle.';
$string['view_ratings_table_explanation'] = 'Diese Tabelle zeigt alle abgegebenen Bewertungen. Eine Bewertung mit einem Rahmen bedeutet, dass der Student ein Mitglied der entsprechenden Gruppe ist.';
$string['your_rating'] = 'Ihre Bewertung';
