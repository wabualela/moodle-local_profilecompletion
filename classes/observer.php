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
 * Event observers for local_profilecompletion.
 *
 * @package    local_profilecompletion
 * @copyright  2026 Moddaker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Evaluate missing fields on login and mark prompt state.
     *
     * @param \core\event\user_loggedin $event
     * @return void
     */
    public static function user_loggedin(\core\event\user_loggedin $event): void {
        global $DB;

        if (!helper::is_enabled()) {
            helper::clear_prompt_pending();
            return;
        }

        if (defined('CLI_SCRIPT') && CLI_SCRIPT) {
            return;
        }

        $userid = (int) ($event->userid ?: $event->objectid ?: $event->relateduserid);
        if ($userid <= 0) {
            helper::clear_prompt_pending();
            return;
        }

        $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], '*', IGNORE_MISSING);
        if (!$user || isguestuser($user)) {
            helper::clear_prompt_pending();
            return;
        }

        $missing = helper::get_missing_fields_for_user($user);
        if (empty($missing)) {
            helper::clear_prompt_pending();
            return;
        }

        helper::mark_prompt_pending();
    }
}
