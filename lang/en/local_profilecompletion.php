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
 * Strings for local_profilecompletion.
 *
 * @package   local_profilecompletion
 * @copyright 2026 Moddaker
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Profile completion prompt';
$string['enabled'] = 'Enable profile completion prompt';
$string['enabled_desc'] = 'Show a post-login prompt when configured profile fields are missing.';
$string['fieldkeys'] = 'Fields to enforce';
$string['fieldkeys_desc'] = 'Choose the core and custom profile fields that must be completed.';
$string['fieldkeycore'] = 'Core: {$a}';
$string['fieldkeycustom'] = 'Custom: {$a->name} ({$a->shortname})';
$string['prompttitle'] = 'Complete your profile';
$string['promptbody'] = 'Some required profile details are still missing. Fill them now to continue with a complete account.';
$string['promptbutton'] = 'Fill missing fields';
$string['modaltitle'] = 'Complete your profile';
$string['savebutton'] = 'Save changes';
$string['customfieldmissing'] = 'The custom profile field "{$a}" does not exist anymore and was ignored.';
$string['savedsuccess'] = 'Profile updated successfully.';
$string['forcefill'] = 'Force fill all configured fields';
$string['forcefill_desc'] = 'When enabled, users are prompted to review and update all configured fields on every login, even if those fields already have values.';
$string['formheader'] = 'Please complete the following profile fields.';
