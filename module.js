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
 * Javascript to set all max size fields in mod_form.php to the same value.
 *
 * @package    mod
 * @subpackage mod_groupdistribution
 * @copyright  2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.mod_groupdistribution = {
    init: function(Y) {
        var button = Y.one('#set_max_size_button');
        var global_size = Y.one('#global_max_size');
        var size_fields = Y.all('#max_size_field');

        button.on('click', function(e) {
            var max_size = parseInt(global_size.get('value'));
            if(max_size > 0) {
                size_fields.set('value', max_size);
            }
        });
    }
}
