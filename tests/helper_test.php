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

namespace local_profilecompletion;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for local_profilecompletion helper.
 *
 * @package    local_profilecompletion
 * @copyright  2026 Moddaker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class helper_test extends \advanced_testcase {
    public function test_get_configured_fieldkeys_filters_invalid_and_duplicates(): void {
        $this->resetAfterTest(true);

        set_config('fieldkeys', 'core:firstname, custom:house,core:firstname,invalid,core:notreal', 'local_profilecompletion');

        $keys = helper::get_configured_fieldkeys();

        $this->assertSame(['core:firstname', 'custom:house'], $keys);
    }

    public function test_get_missing_fields_for_user_with_core_and_custom(): void {
        $this->resetAfterTest(true);
        global $CFG;

        require_once($CFG->dirroot . '/user/profile/lib.php');

        $generator = $this->getDataGenerator();
        $generator->create_custom_profile_field([
            'datatype' => 'text',
            'shortname' => 'house',
            'name' => 'House',
        ]);

        $user = $generator->create_user([
            'city' => '',
            'country' => 'SA',
        ]);

        set_config('fieldkeys', 'core:city,custom:house', 'local_profilecompletion');

        $missing = helper::get_missing_fields_for_user($user);
        $this->assertArrayHasKey('core:city', $missing);
        $this->assertArrayHasKey('custom:house', $missing);

        user_update_user((object) ['id' => $user->id, 'city' => 'Riyadh'], false);
        profile_save_data((object) ['id' => $user->id, 'profile_field_house' => 'Gryffindor']);

        $updated = \core_user::get_user($user->id, '*', MUST_EXIST);
        $missing = helper::get_missing_fields_for_user($updated);
        $this->assertSame([], $missing);
    }

    public function test_phone1_fieldkey_is_valid_and_detected_as_missing(): void {
        $this->resetAfterTest(true);

        // phone1 contains a digit so the regex must allow [a-z0-9_].
        $this->assertTrue(helper::is_valid_fieldkey('core:phone1'));

        $user = $this->getDataGenerator()->create_user(['phone1' => '']);
        set_config('fieldkeys', 'core:phone1', 'local_profilecompletion');

        $keys = helper::get_configured_fieldkeys();
        $this->assertContains('core:phone1', $keys);

        $missing = helper::get_missing_fields_for_user($user);
        $this->assertArrayHasKey('core:phone1', $missing);
        $this->assertSame('core', $missing['core:phone1']['type']);
        $this->assertSame('phone1', $missing['core:phone1']['fieldname']);
    }

    public function test_unknown_custom_key_is_ignored(): void {
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        set_config('fieldkeys', 'custom:doesnotexist', 'local_profilecompletion');

        $missing = helper::get_missing_fields_for_user($user);
        $this->assertSame([], $missing);
    }
}
