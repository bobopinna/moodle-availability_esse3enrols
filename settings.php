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
 * Esse3enrols plugin settings
 *
 * @package availability_esse3enrols
 * @copyright 2022 Roberto Pinna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('esse3enrols_settings',
        get_string('esse3enrols_settings', 'availability_esse3enrols'), ''));

$choices = array();
$choices['username'] = get_string('username');
$choices['email'] = get_string('email');
$choices['city'] = get_string('city');
$choices['country'] = get_string('state');
$choices['idnumber'] = get_string('idnumber');
$choices['institution'] = get_string('institution');
$choices['department'] = get_string('department');
$choices['phone'] = get_string('phone');
$choices['phone2'] = get_string('phone2');
$choices['address'] = get_string('address');

$customfields = $DB->get_records('user_info_field');
if (!empty($customfields)) {
    foreach ($customfields as $customfield) {
        $choices['profile_' . $customfield->shortname] = $customfield->name;
    }
}
$settings->add(new admin_setting_configselect('availability_esse3enrols/field', get_string('field', 'availability_esse3enrols'),
        get_string('configfield', 'availability_esse3enrols'), 'idnumber', $choices));

