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
 * Condition main class.
 *
 * @package availability_esse3enrols
 * @copyright 2022 Roberto Pinna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_esse3enrols;

/**
 * Condition main class.
 *
 * @package availability_esse3enrols
 * @copyright 2022 Roberto Pinna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /** @var array of idnumbers that this condition requires */
    protected $idnumbers;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        // Get idnumbers.
        if (!property_exists($structure, 'idnumbers')) {
            $this->idnumbers = array();
        } else if (!empty($structure->idnumbers)) {
            $this->idnumbers = explode(',', $structure->idnumbers);
        } else {
            throw new \coding_exception('Invalid ->idnumbers for Esse3 enrolments condition');
        }
    }

    /**
     * Saves data back to a structure object.
     *
     * @return \stdClass Structure object
     */
    public function save() {
        $result = (object)['type' => 'esse3enrols'];
        if ($this->idnumbers) {
            $result->idnumbers = implode(',', $this->idnumbers);
        }
        return $result;
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param string $idnumbers List of enabled idnumbers
     * @return stdClass Object representing condition
     */
    public static function get_json($idnumbers = array()) {
        return (object)['type' => 'esse3enrols', 'idnumbers' => $idnumbers];
    }

    /**
     * Determines whether a particular item is currently available
     * according to this availability condition.
     *
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @param bool $grabthelot Performance hint: if true, caches information
     *   required for all course-modules, to make the front page and similar
     *   pages work more quickly (works only for current user)
     * @param int $userid User ID to check availability for
     * @return bool True if available
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $CFG, $DB, $USER;

        $allow = false;
        $useridnumber = '';
        if (($userid == $USER->id) && isset($USER->idnumber)) {
            // Checking the idnumber method of the currently logged in user, so do not
            // default to the account idnumber, because the session idnumber may be different.
            $useridnumber = $USER->idnumber;
        } else {
            if (!is_null($userid)) {
                // Checking access for someone else than the logged in user, so
                // use the idnumber of that user account.
                $useridnumber = $DB->get_field('user', 'idnumber', ['id' => $userid]);
            }
        }
        if (in_array($useridnumber, $this->idnumbers)) {
            $allow = true;
        }
        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies). Used to obtain information that is displayed to
     * students if the activity is not available to them, and for staff to see
     * what conditions are.
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @return string Information string (for admin) about all restrictions on this item
     */
    public function get_description($full, $not, \core_availability\info $info) {
        if (!empty($this->idnumbers)) {
            $snot = $not ? 'not' : '';
            return get_string('getdescription' .$snot, 'availability_esse3enrols', implode(', ', $this->idnumbers));
        }
        return '';
    }

    /**
     * Obtains a representation of the options of this condition as a string,
     * for debugging.
     *
     * @return string Text representation of parameters
     */
    protected function get_debug_string() {
        return implode(', ', $this->idnumbers) ?? 'any';
    }

}
