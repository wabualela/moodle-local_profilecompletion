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

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Utility helpers for profile completion prompt logic.
 *
 * @package    local_profilecompletion
 * @copyright  2026 Moddaker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /** @var string */
    public const SESSION_PROMPT_PENDING = 'local_profilecompletion_prompt_pending';

    /** @var array */
    public const CORE_FIELDS = ['firstname', 'lastname', 'email', 'city', 'country', 'phone1'];

    /**
     * Whether plugin is enabled.
     *
     * @return bool
     */
    public static function is_enabled(): bool {
        $enabled = get_config('local_profilecompletion', 'enabled');
        if ($enabled === false || $enabled === null) {
            return true;
        }
        return (bool) $enabled;
    }

    /**
     * Build options list for admin multiselect setting.
     *
     * @return array
     */
    public static function get_available_field_options(): array {
        require_once(__DIR__ . '/../../../user/profile/lib.php');

        $options = [];
        foreach (self::CORE_FIELDS as $fieldname) {
            $options['core:' . $fieldname] = get_string('fieldkeycore', 'local_profilecompletion', get_string($fieldname));
        }

        $customfields = profile_get_custom_fields(false);
        foreach ($customfields as $customfield) {
            $a = (object) [
                'name' => format_string($customfield->name),
                'shortname' => $customfield->shortname,
            ];
            $options['custom:' . $customfield->shortname] = get_string('fieldkeycustom', 'local_profilecompletion', $a);
        }

        return $options;
    }

    /**
     * Parse configured field keys.
     *
     * @return array
     */
    public static function get_configured_fieldkeys(): array {
        $raw = get_config('local_profilecompletion', 'fieldkeys');

        if (is_array($raw)) {
            $keys = $raw;
        } else {
            $keys = preg_split('/\s*,\s*/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY);
        }

        $validated = [];
        foreach ($keys as $key) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }
            if (self::is_valid_fieldkey($key)) {
                $validated[$key] = true;
            }
        }

        return array_keys($validated);
    }

    /**
     * Validate a configured field key.
     *
     * @param string $key
     * @return bool
     */
    public static function is_valid_fieldkey(string $key): bool {
        if (preg_match('/^core:([a-z0-9_]+)$/', $key, $matches)) {
            return in_array($matches[1], self::CORE_FIELDS, true);
        }

        if (preg_match('/^custom:([a-z0-9_]+)$/', $key)) {
            return true;
        }

        return false;
    }

    /**
     * Resolve missing configured fields for a user.
     *
     * @param stdClass $user
     * @return array
     */
    public static function get_missing_fields_for_user(stdClass $user): array {
        require_once(__DIR__ . '/../../../user/profile/lib.php');

        $missing = [];
        if (empty($user->id) || isguestuser($user)) {
            return $missing;
        }

        $configured = self::get_configured_fieldkeys();
        if (empty($configured)) {
            return $missing;
        }

        $customshortnames = [];
        foreach ($configured as $fieldkey) {
            if (str_starts_with($fieldkey, 'custom:')) {
                $customshortnames[] = substr($fieldkey, 7);
            }
        }

        $profilefields = [];
        if (!empty($customshortnames)) {
            foreach (profile_get_user_fields_with_data((int) $user->id) as $profilefield) {
                $profilefields[$profilefield->field->shortname] = $profilefield;
            }
        }

        foreach ($configured as $fieldkey) {
            if (str_starts_with($fieldkey, 'core:')) {
                $fieldname = substr($fieldkey, 5);
                $value = isset($user->{$fieldname}) ? trim((string) $user->{$fieldname}) : '';
                if ($value === '') {
                    $missing[$fieldkey] = [
                        'key' => $fieldkey,
                        'type' => 'core',
                        'fieldname' => $fieldname,
                        'label' => get_string($fieldname),
                    ];
                }
                continue;
            }

            if (str_starts_with($fieldkey, 'custom:')) {
                $shortname = substr($fieldkey, 7);
                if (!isset($profilefields[$shortname])) {
                    continue;
                }

                $profilefield = $profilefields[$shortname];
                if ($profilefield->is_empty()) {
                    $missing[$fieldkey] = [
                        'key' => $fieldkey,
                        'type' => 'custom',
                        'shortname' => $shortname,
                        'label' => $profilefield->display_name(),
                    ];
                }
            }
        }

        return $missing;
    }

    /**
     * Mark prompt pending for session.
     *
     * @return void
     */
    public static function mark_prompt_pending(): void {
        global $SESSION;

        $SESSION->{self::SESSION_PROMPT_PENDING} = true;
    }

    /**
     * Clear prompt pending flag for session.
     *
     * @return void
     */
    public static function clear_prompt_pending(): void {
        global $SESSION;

        unset($SESSION->{self::SESSION_PROMPT_PENDING});
    }

    /**
     * Whether prompt is marked pending in session.
     *
     * @return bool
     */
    public static function is_prompt_pending(): bool {
        global $SESSION;

        return !empty($SESSION->{self::SESSION_PROMPT_PENDING});
    }
}
