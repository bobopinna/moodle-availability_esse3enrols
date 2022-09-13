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

        $iscurrentuser = $USER->id == $userid;
        if (empty($userid) || isguestuser($userid) || ($iscurrentuser && !isloggedin())) {
            // Must be logged in and can't be the guest.
            return false;
        }

        $allow = false;

        $checkfield = get_config('availability_esse3enrols', 'field');
        if (empty($checkfield)) {
            $checkfield = 'idnumber';
        }

        $iscustomfield = false;
        // Check for a custom profile field.
        $customprefix = 'profile_';
        $customfield = null;
        if (substr($checkfield, 0, strlen($customprefix)) == $customprefix) {
            $checkfield = substr($checkfield, strlen($customprefix));
            require_once($CFG->dirroot . '/user/profile/lib.php');
            $customfield = profile_get_custom_field_data_by_shortname($checkfield);
            if ($customfield !== null) {
                $iscustomfield = true;
            }
            if (!$iscustomfield) {
                // Custom field no more defined we could skip any other tests.
                return false;
            }
        }

        $userfield = '';
        if ($iscurrentuser) {
            // Check for the logged in user.
            if ($iscustomfield) {
                // Checking if the custom profile fields are already available.
                if (!isset($USER->profile)) {
                    // Drat! they're not. We need to use a temp object and load them.
                    // We don't use $USER as the profile fields are loaded into the object.
                    $user = new \stdClass;
                    $user->id = $USER->id;
                    // This should ALWAYS be set, but just in case we check.
                    require_once($CFG->dirroot . '/user/profile/lib.php');
                    profile_load_custom_fields($user);
                    if (array_key_exists($checkfield, $user->profile)) {
                        $userfield = $user->profile[$checkfield];
                    }
                } else if (array_key_exists($checkfield, $USER->profile)) {
                    // Hurrah they're available, this is easy.
                    $userfield = $USER->profile[$checkfield];
                }
            } else {
                if (isset($USER->{$checkfield})) {
                    $userfield = $USER->{$checkfield};
                } else {
                    // Unknown user field. This should not happen.
                    throw new \coding_exception('Requested user profile field does not exist');
                }
            }
        } else {
            // Checking access for someone else than the logged in user.
            if ($iscustomfield) {
                $userprofiledata = $DB->get_field('user_info_data', 'data',
                        array('userid' => $userid, 'fieldid' => $customfield->id), IGNORE_MiSSING);
                if ($userprofiledata !== false) {
                    $userfield = $userprofiledata;
                } else {
                    $userfield = $customfield->defaultdata;
                }
            } else {
                $userprofiledata = $DB->get_field('user', $checkfield, array('id' => $userid), MUST_EXISTS);
                if ($userprofiledata !== false) {
                    $userfield = $userprofiledata;
                } else {
                    // Unknown user field. This should not happen.
                    throw new \coding_exception('Requested user profile field does not exist');
                }
            }
        }
        if (in_array($userfield, $this->idnumbers)) {
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
        global $CFG;

        if (!empty($this->idnumbers)) {

            $checkfield = get_config('availability_esse3enrols', 'field');
            if (empty($checkfield)) {
                $checkfield = 'idnumber';
            }

            // Check for a custom profile field.
            $customprefix = 'profile_';
            $customfield = null;
            $translatedfieldname = '';
            if (substr($checkfield, 0, strlen($customprefix)) == $customprefix) {
                $checkfield = substr($checkfield, strlen($customprefix));
                require_once($CFG->dirroot . '/user/profile/lib.php');
                $customfield = profile_get_custom_field_data_by_shortname($checkfield);
                if ($customfield !== null) {
                    $translatedfieldname = $customfield->name;
                } else {
                    $translatedfieldname = get_string('missing', 'availability_profile', $checkfield);
                }
            } else {
                if (class_exists('\core_user\fields')) {
                    $translatedfieldname = \core_user\fields::get_display_name($checkfield);
                } else {
                    $translatedfieldname = get_user_field_name($checkfield);
                }
            }

            $a = new \stdClass();
            $a->values = implode(', ', $this->idnumbers);
            if (function_exists('self::description_format_string')) {
                $a->field = self::description_format_string($translatedfieldname);
            } else {
                $course = $info->get_course();
                $context = \context_course::instance($course->id);
                $a->field = format_string($translatedfieldname, true, array('context' => $context));
            }

            $snot = $not ? 'not' : '';
            return get_string('getdescription' .$snot, 'availability_esse3enrols', $a);
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
