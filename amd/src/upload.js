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
 * upload.js
 *
 * @package   local_geniaicourse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function () {
    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    };

    const disableOnSubmit = (form, submit, text) => {
        if (!form || !submit) {
            return;
        }
        form.addEventListener('submit', function () {
            submit.disabled = true;
            submit.textContent = text || submit.textContent;
        });
    };

    return {
        init: function () {
            const input = document.getElementById('geniaicourse-files');
            const container = document.getElementById('geniaicourse-fileinstructions');
            const form = document.getElementById('geniaicourse-form');
            const submit = document.getElementById('geniaicourse-submit');
            if (!input || !container) {
                return;
            }

            input.addEventListener('change', function () {
                container.innerHTML = '';
                Array.from(input.files).forEach(function (file, index) {
                    const card = document.createElement('div');
                    card.className = 'geniaicourse-fileitem';
                    card.innerHTML =
                        '<div class="geniaicourse-filename">' + escapeHtml(file.name) + '</div>' +
                        '<input class="form-control" type="text" name="fileinstructions[' + index + ']" ' +
                        'placeholder="' + escapeHtml(input.dataset.instructionplaceholder || '') + '">';
                    container.appendChild(card);
                });
            });

            disableOnSubmit(form, submit, submit ? submit.dataset.analyzing : '');
        },

        initReview: function () {
            const form = document.getElementById('geniaicourse-create-form');
            const submit = document.getElementById('geniaicourse-create-submit');
            disableOnSubmit(form, submit, submit ? submit.dataset.creating : '');
        }
    };
});
