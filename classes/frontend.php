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
 * Front-end class.
 *
 * @package availability_esse3enrols
 * @copyright 2022 Roberto Pinna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_esse3enrols;

/**
 * Front-end class.
 *
 * @package availability_esse3enrols
 * @copyright 2022 Roberto Pinna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {

    /**
     * Additional parameters for the plugin's initInner function.
     *
     * Returns an array of array of id.
     *
     * @param stdClass $course Course object
     * @param cm_info $cm Course-module currently being edited (null if none)
     * @param section_info $section Section currently being edited (null if none)
     * @return array Array of parameters for the JavaScript function
     */
    protected function get_javascript_init_params($course, \cm_info $cm = null, \section_info $section = null) {
        global $PAGE;

        $PAGE->requires->js(new \moodle_url('https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js'), true);

        $idcolumn = 'Matricola';

        $idnumberstring = \availability_esse3enrols\condition::get_translated_checkfield();

        return array($idcolumn, $idnumberstring);
    }

    /**
     * Returns an array of strings used in javascript frontend.
     *
     * @return array Array of translated strings
     */
    protected function get_javascript_strings() {
        return array('missing');
    }
}
