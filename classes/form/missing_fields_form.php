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

namespace local_profilecompletion\form;

use context;
use core_form\dynamic_form;
use local_profilecompletion\helper;
use moodle_exception;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Dynamic modal form to fill configured missing profile fields.
 *
 * @package    local_profilecompletion
 * @copyright  2026 Moddaker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class missing_fields_form extends dynamic_form {
    /** @var array|null */
    protected ?array $missingfields = null;

    /**
     * Return current user's missing configured fields.
     *
     * @return array
     */
    protected function get_missing_fields(): array {
        global $USER;

        if ($this->missingfields !== null) {
            return $this->missingfields;
        }

        $this->missingfields = helper::get_missing_fields_for_user($USER);
        return $this->missingfields;
    }

    #[\Override]
    public function definition(): void {
        global $USER;

        require_once(__DIR__ . '/../../../../user/profile/lib.php');

        $mform = $this->_form;
        $missing = $this->get_missing_fields();

        $profilefields = [];
        foreach (profile_get_user_fields_with_data((int) $USER->id) as $profilefield) {
            $profilefields[$profilefield->field->shortname] = $profilefield;
        }

        foreach ($missing as $field) {
            if ($field['type'] === 'core') {
                $this->add_core_element($mform, $field['fieldname'], $field['label']);
                continue;
            }

            if ($field['type'] === 'custom') {
                $shortname = $field['shortname'];
                if (!isset($profilefields[$shortname])) {
                    continue;
                }
                $profilefield = $profilefields[$shortname];
                $profilefield->edit_field_add($mform);
                $profilefield->edit_field_set_default($mform);
                $mform->addRule('profile_field_' . $shortname, get_string('required'), 'required', null, 'client');
            }
        }
    }

    /**
     * Add one core element.
     *
     * @param \MoodleQuickForm $mform
     * @param string $fieldname
     * @param string $label
     * @return void
     */
    protected function add_core_element(\MoodleQuickForm $mform, string $fieldname, string $label): void {
        switch ($fieldname) {
            case 'firstname':
            case 'lastname':
                $mform->addElement('text', $fieldname, $label);
                $mform->setType($fieldname, PARAM_NOTAGS);
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
                break;

            case 'email':
                $mform->addElement('text', 'email', $label);
                $mform->setType('email', PARAM_EMAIL);
                $mform->addRule('email', get_string('required'), 'required', null, 'client');
                break;

            case 'city':
                $mform->addElement('text', 'city', $label);
                $mform->setType('city', PARAM_TEXT);
                $mform->addRule('city', get_string('required'), 'required', null, 'client');
                break;

            case 'country':
                $countries = get_string_manager()->get_list_of_countries();
                $mform->addElement('select', 'country', $label, ['' => ''] + $countries);
                $mform->addRule('country', get_string('required'), 'required', null, 'client');
                break;

            case 'phone1':
                $mform->addElement('text', 'phone1', $label, 'maxlength="20" size="25"');
                $mform->setType('phone1', \core_user::get_property_type('phone1'));
                $mform->setForceLtr('phone1');
                $mform->addRule('phone1', get_string('required'), 'required', null, 'client');
                break;
        }
    }

    #[\Override]
    public function validation($data, $files): array {
        global $USER;

        require_once(__DIR__ . '/../../../../user/profile/lib.php');

        $errors = parent::validation($data, $files);
        $missing = $this->get_missing_fields();

        $usernew = (object) $data;
        $usernew->id = $USER->id;

        $profilefields = [];
        foreach (profile_get_user_fields_with_data((int) $USER->id) as $profilefield) {
            $profilefields[$profilefield->field->shortname] = $profilefield;
        }

        foreach ($missing as $field) {
            if ($field['type'] === 'core') {
                $fieldname = $field['fieldname'];
                $value = isset($data[$fieldname]) ? trim((string) $data[$fieldname]) : '';
                if ($value === '') {
                    $errors[$fieldname] = get_string('required');
                }
                if ($fieldname === 'email' && $value !== '' && !validate_email($value)) {
                    $errors['email'] = get_string('invalidemail');
                }
                continue;
            }

            if ($field['type'] === 'custom') {
                $shortname = $field['shortname'];
                if (!isset($profilefields[$shortname])) {
                    continue;
                }
                $inputname = 'profile_field_' . $shortname;
                $value = $data[$inputname] ?? '';
                if ((is_string($value) && trim($value) === '') || $value === null) {
                    $errors[$inputname] = get_string('required');
                }
                $errors += $profilefields[$shortname]->edit_validate_field($usernew);
            }
        }

        return $errors;
    }

    #[\Override]
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    #[\Override]
    protected function check_access_for_dynamic_submission(): void {
        global $USER;

        require_login();

        if (!isloggedin() || isguestuser($USER)) {
            throw new moodle_exception('noguest');
        }

        $usercontext = \context_user::instance((int) $USER->id);
        $systemcontext = \context_system::instance();
        $caneditown = has_capability('moodle/user:editownprofile', $usercontext, $USER);
        $canupdateusers = has_capability('moodle/user:update', $systemcontext, $USER);

        if (!$caneditown && !$canupdateusers) {
            throw new moodle_exception('nopermission');
        }
    }

    #[\Override]
    public function process_dynamic_submission(): array {
        global $USER;

        require_once(__DIR__ . '/../../../../user/profile/lib.php');
        require_once(__DIR__ . '/../../../../user/lib.php');

        $data = $this->get_data();
        if (!$data) {
            return ['success' => false];
        }

        $missing = $this->get_missing_fields();

        $userupdate = (object) ['id' => (int) $USER->id];
        $hascoreupdates = false;
        foreach ($missing as $field) {
            if ($field['type'] !== 'core') {
                continue;
            }
            $fieldname = $field['fieldname'];
            if (property_exists($data, $fieldname)) {
                $userupdate->{$fieldname} = $data->{$fieldname};
                $hascoreupdates = true;
            }
        }

        if ($hascoreupdates) {
            user_update_user($userupdate, false);
        }

        $profilepayload = (object) ['id' => (int) $USER->id];
        $hascustomupdates = false;
        foreach ($missing as $field) {
            if ($field['type'] !== 'custom') {
                continue;
            }
            $inputname = 'profile_field_' . $field['shortname'];
            if (property_exists($data, $inputname)) {
                $profilepayload->{$inputname} = $data->{$inputname};
                $hascustomupdates = true;
            }
        }

        if ($hascustomupdates) {
            profile_save_data($profilepayload);
        }

        if (empty(helper::get_missing_fields_for_user(\core_user::get_user((int) $USER->id, '*', MUST_EXIST)))) {
            helper::clear_prompt_pending();
        }

        return ['success' => true];
    }

    #[\Override]
    public function set_data_for_dynamic_submission(): void {
        global $USER;

        $data = new \stdClass();
        $missing = $this->get_missing_fields();

        foreach ($missing as $field) {
            if ($field['type'] === 'core') {
                $fieldname = $field['fieldname'];
                $data->{$fieldname} = $USER->{$fieldname} ?? '';
            }
        }

        $this->set_data($data);
    }

    #[\Override]
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        global $USER;

        return new moodle_url('/user/edit.php', ['id' => (int) $USER->id]);
    }
}
