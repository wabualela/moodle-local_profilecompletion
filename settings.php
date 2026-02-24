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
 * Admin settings for local_profilecompletion.
 *
 * @package   local_profilecompletion
 * @copyright 2026 Moddaker
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_profilecompletion\helper;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_profilecompletion', get_string('pluginname', 'local_profilecompletion'));

    $settings->add(new admin_setting_configcheckbox(
        'local_profilecompletion/enabled',
        get_string('enabled', 'local_profilecompletion'),
        get_string('enabled_desc', 'local_profilecompletion'),
        1
    ));

    $settings->add(new admin_setting_configmultiselect(
        'local_profilecompletion/fieldkeys',
        get_string('fieldkeys', 'local_profilecompletion'),
        get_string('fieldkeys_desc', 'local_profilecompletion'),
        [],
        helper::get_available_field_options()
    ));

    $ADMIN->add('localplugins', $settings);
}
