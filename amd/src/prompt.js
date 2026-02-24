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
 * Modal launcher for local_profilecompletion.
 *
 * @module     local_profilecompletion/prompt
 * @copyright  2026 Moddaker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import * as Toast from 'core/toast';

let handlerBound = false;
let activeConfig = null;

/**
 * Escape plain text for HTML interpolation.
 *
 * @param {string} value
 * @returns {string}
 */
const escapeHtml = (value) => {
    const el = document.createElement('div');
    el.textContent = value;
    return el.innerHTML;
};

/**
 * Build toast body with action button.
 *
 * @param {Object} config
 * @returns {string}
 */
const getToastMessage = (config) => {
    return '<div>' + escapeHtml(config.toastbody || '') + '</div>' +
        '<div class="mt-2">' +
        '<button type="button" class="btn btn-primary btn-sm local-profilecompletion-toast-action">' +
        escapeHtml(config.buttontext || '') +
        '</button>' +
        '</div>';
};

/**
 * Open profile completion modal.
 *
 * @param {Object} config
 * @param {HTMLElement|null} returnFocus
 */
const openModal = (config, returnFocus = null) => {
    const modalForm = new ModalForm({
        modalConfig: {
            title: config.modaltitle,
            large: true,
            isVerticallyCentered: true,
        },
        formClass: config.formclass,
        saveButtonText: config.savebuttontext,
        returnFocus: returnFocus || document.body,
    });

    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => {
        window.location.reload();
    });

    modalForm.show();
};

/**
 * Initialise profile completion prompt button.
 *
 * @param {Object} config
 */
export const init = (config) => {
    if (!config) {
        return;
    }

    activeConfig = config;

    if (!handlerBound) {
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('.local-profilecompletion-toast-action');
            if (!trigger || !activeConfig) {
                return;
            }

            e.preventDefault();
            openModal(activeConfig, trigger);
        });
        handlerBound = true;
    }

    Toast.add(getToastMessage(config), {
        title: escapeHtml(config.toasttitle || ''),
        type: 'warning',
        autohide: true,
        delay: Number(config.toastdelay) || 6000,
        closeButton: true,
    });
};
