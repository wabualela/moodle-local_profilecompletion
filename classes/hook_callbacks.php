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
 * Hook callback handlers for local_profilecompletion.
 *
 * @package    local_profilecompletion
 * @copyright  2026 Moddaker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Render missing-fields notification near top of page body.
     *
     * The notification HTML is injected hidden at the top of the <body> via add_html().
     * The AMD module then repositions it into the page content area and shows it.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     * @return void
     */
    public static function show_missing_fields_prompt(
        \core\hook\output\before_standard_top_of_body_html_generation $hook,
    ): void {
        global $USER, $PAGE;

        if (!helper::is_enabled() || !helper::is_prompt_pending()) {
            return;
        }

        if ((defined('CLI_SCRIPT') && CLI_SCRIPT) || (defined('AJAX_SCRIPT') && AJAX_SCRIPT)) {
            return;
        }

        if (!isloggedin() || isguestuser()) {
            return;
        }

        if ($PAGE->pagelayout === 'login') {
            return;
        }

        $missing = helper::get_missing_fields_for_user($USER);
        if (empty($missing)) {
            helper::clear_prompt_pending();
            return;
        }

        // Render the notification element hidden at the top of <body>.
        // The AMD module will move it into #page-content and make it visible.
        $hook->add_html($hook->renderer->render_from_template(
            'local_profilecompletion/notification',
            [
                'title'      => get_string('prompttitle', 'local_profilecompletion'),
                'body'       => get_string('promptbody', 'local_profilecompletion'),
                'buttontext' => get_string('promptbutton', 'local_profilecompletion'),
            ]
        ));

        // Load AMD module to reposition the notification and open the modal on click.
        $PAGE->requires->js_call_amd('local_profilecompletion/prompt', 'init', [[
            'modaltitle'    => get_string('modaltitle', 'local_profilecompletion'),
            'savebuttontext' => get_string('savebutton', 'local_profilecompletion'),
            'formclass'     => \local_profilecompletion\form\missing_fields_form::class,
        ]]);
    }
}
