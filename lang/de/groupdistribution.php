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
 * @package    mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['at_least_two'] = 'Bitte gib mindestens zwei Bewertungen ab, die besser sind als "unmöglich".';
$string['at_least_two_groups'] = 'Ein Kurs muss mindestens zwei Gruppen enthalten damit Groupdistribution funktionieren kann.';
$string['at_least_two_rateable_groups'] = 'Ein Kurs muss mindestens zwei bewertbare Gruppen enthalten.';
$string['begindate'] = 'Bewertung beginnt am:';
$string['changes'] = 'Seit {$a->time}, gab es folgende Änderungen: {$a->changes}';
$string['description_form'] = "Beschreibung der Gruppe";
$string['description_overrides'] = 'Überschreibt die Beschreibung der Gruppe';
$string['description_overrides_help'] = 'Dieses Feld zeigt die Beschreibung der Gruppe. Wenn du die deine Änderungen speicherst, wird die Beschreibung der Gruppe überschrieben.';
$string['distribution_saved'] = 'Verteilung gespeichert.';
$string['distribution_table'] = 'Zeigt wie viele Studenten in eine Gruppe mit einer bestimmten Bewertung gekommen sind.';
$string['enddate'] = 'Bewertung endet am:';
$string['global_max_size'] = 'Setzt die maximale Anzahl an Studenten jeder Gruppe auf diesen Wert.';
$string['group'] = 'Gruppe';
$string['group_added_to_rating'] = 'Gruppe zur Bewertung hinzugefügt';
$string['group_description'] = 'Beschreibung der Gruppe:';
$string['group_description_changed'] = 'Beschreibung einer Gruppe geändert';
$string['group_not_in_course'] = 'Eine der Gruppen gehört nicht zum Kurs.';
$string['group_picture'] = 'Bild der Gruppe:';
$string['group_removed_from_rating'] = 'Gruppe von der Bewertung entfernt';
$string['group_teachers'] = 'Lehrer der Gruppe:';
$string['groupdistribution'] = 'Groupdistribution';
$string['groupdistribution:addinstance'] = 'Erstellt eine neue Groupdistribution';
$string['groupdistribution:give_rating'] = 'Gibt Bewertungen für Gruppen ab';
$string['groupdistribution:group_teacher'] = 'Lehrer der für eine Gruppe zuständig ist';
$string['groupdistribution:start_distribution'] = 'Startet den Verteilungsalgorithmus';
$string['groupdistribution_date_changed'] = 'Bewertungsperiode hat sich geändert';
$string['groupdistribution_name'] = 'Name der Groupdistribution';
$string['invalid_dates'] = 'Das Beginndatum muss vor dem Enddatum sein!';
$string['invalid_path'] = 'Unerlaubter Pfad!';
$string['max_timelimit'] = 'Zeitlimit des Groupdistribution Algorithmus in Sekunden';
$string['max_timelimit_description'] = 'Dies gibt an, nach wie vielen Sekunden der Verteilungsalgorithmus von PHP unterbrochen wird. Wenn Lehrer mehr Zeit brauchen, um ihre Verteilungen zu berechnen kannst du diesen Wert erhöhen.';
$string['maxsize_form'] = 'Maximale Anzahl an Studenten in einer Gruppe';
$string['maxsize_setting'] = 'Standardwert für die maximale Anzahl an Studenten in einer Gruppe';
$string['maxsize_setting_description'] = 'Dies ist der Standardwert für die maximale Anzahl an Studenten pro Gruppe auf der Groupdistribution Einstellungsseite.';
$string['modulename'] = 'Groupdistribution';
$string['modulename_help'] = 'Groupdistribution lässt Studenten, während einer Bewertungsperiode, Bewertungen für Gruppen angeben. Nach dieser Periode verteilt ein Algorithmus die Studenten auf die Gruppen gemäß ihren Bewertungen.';
$string['modulenameplural'] = 'Groupdistributions';
$string['negative_cycle'] = 'Negativer Zyklus entdeckt!';
$string['not_enrolled'] = 'Du kannst Groupdistribution nicht verwenden, weil du kein Mitglied dieses Kurses bist.';
$string['no_groups_to_rate'] = 'Es gibt keine Gruppen die du eine Bewertung abgeben kannst. Frage deinen Lehrer, dass er einige hinzufügt.';
$string['no_rating_given'] = 'Nichts';
$string['only_one_per_course'] = 'Es kann nur eine Groupdistribution pro Kurs geben!';
$string['pluginadministration'] = 'Groupdistribution-Verwaltung';
$string['pluginname'] = 'Groupdistribution';
$string['rate_group'] = "Du kannst deine Bewertung ändern";
$string['rate_group_help'] = "Je besser die Bewertung, desto höher ist deine Chance in die Gruppe zu kommen.";
$string['rate_group_not_saved'] = "Du hast diese Gruppe noch nicht bewertet.";
$string['rateable_form'] = 'Können Studenten diese Gruppe über Groupdistribution sehen und bewerten?';
$string['rating_bad'] = "Schlecht";
$string['rating_best'] = "Sehr gut";
$string['rating_good'] = "Gut";
$string['rating_has_begun'] = 'Die Bewertungsperiode hat begonnen und geht bis: {$a->until}.';
$string['rating_impossible'] = "Unmöglich";
$string['rating_is_over'] = 'Die Bewertungsperiode ist vorbei.';
$string['rating_ok'] = "Ok";
$string['rating_worst'] = "Sehr schlecht";
$string['ratings_saved'] = 'Deine Bewertungen wurden gespeichert.';
$string['ratings_table'] = 'Diese Tabelle zeigt alle abgegebenen Bewertungen. Eine Bewertung mit einem Rahmen bedeutet, dass der Student ein Mitglied der entsprechenden Gruppe ist.';
$string['set_max_size_button'] = 'Ändere alle maximalen Studenten zahlen';
$string['show_names'] = 'Zeige die Namen der Studenten in der Bewertungstabelle';
$string['show_names_description'] = 'Du kannst die Privatsphäre der Studenten schützen, indem du ihre Namen in der Bewertungstabelle versteckst. Dies verhindert, dass Lehrer erfahren welche Bewertungen von welchem Studenten stammen.';
$string['show_rating_period'] = 'die Bewertungsperiode beginnt am {$a->begin} und endet am {$a->end}';
$string['show_table'] = 'Zeige die Bewertungstabelle';
$string['start_distribution'] = "Starte den Verteilungsalgorithmus";
$string['start_distribution_explanation'] = 'Startet den Verteilungsalgorithmus. Dies kann etwas dauern.';
$string['too_early_to_distribute'] = 'Du kannst den Verteilungsalgorithmus starten, wenn die Bewertungsperiode vorbei ist.';
$string['too_early_to_rate'] = 'Du kannst deine Bewertung noch nicht abgeben. Komm bitte wieder, wenn die Bewertungsperiode begonnen hat.';
$string['other_changes'] = 'Andere Änderungen';
$string['unassigned_users'] = 'Studenten ohne Gruppe';
$string['view_distribution_table'] = 'Zeigt die abgegebenen Bewertungen aller Studenten in einer Tabelle.';
$string['you_must_reinstall'] = 'Bitte installiere Groupdistribution neu. Es ist nicht möglicher von dieser Alpha-Version aus upzudaten!';
