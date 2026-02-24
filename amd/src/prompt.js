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
 * Profile completion inline notification handler.
 *
 * The PHP hook renders the notification element hidden at the top of <body>.
 * This module moves it into the page content area and shows it,
 * matching the position of the theme-checkplugins block.
 *
 * @module     local_profilecompletion/prompt
 * @copyright  2026 Moddaker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';

/** Selector for the hidden notification element rendered by the hook. */
const NOTIFICATION_SELECTOR = '[data-region="local-profilecompletion-notification"]';

/**
 * Candidate selectors for the content-area insertion point, tried in order.
 * - #page-content   — mb2nl theme main content wrapper
 * - #region-main    — Moodle standard region
 * - [data-region="main-content"] — Boost / other themes
 */
const CONTENT_SELECTORS = [
    '#page-content',
    '#region-main',
    '[data-region="main-content"]',
];

/**
 * Find the best available content-area container.
 *
 * @returns {Element|null}
 */
const findContentArea = () => {
    for (const selector of CONTENT_SELECTORS) {
        const el = document.querySelector(selector);
        if (el) {
            return el;
        }
    }
    return null;
};

/**
 * Open the profile-completion modal form.
 *
 * @param {Object}       config
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
 * Initialise the profile-completion inline notification.
 *
 * Moves the hidden notification element into the page content area,
 * makes it visible, and wires the "fill missing fields" button to the modal.
 *
 * @param {Object} config
 * @param {string} config.modaltitle
 * @param {string} config.savebuttontext
 * @param {string} config.formclass
 */
export const init = (config) => {
    if (!config) {
        return;
    }

    const notificationEl = document.querySelector(NOTIFICATION_SELECTOR);
    if (!notificationEl) {
        return;
    }

    // Move the notification from the top of <body> into the content area.
    const contentArea = findContentArea();
    if (contentArea) {
        contentArea.prepend(notificationEl);
    }

    // Show the notification now that it is in the right place.
    notificationEl.style.display = '';

    // Handle the "fill missing fields" button click.
    notificationEl.addEventListener('click', (e) => {
        const trigger = e.target.closest('.local-profilecompletion-open');
        if (!trigger) {
            return;
        }
        e.preventDefault();
        openModal(config, trigger);
    });
};
