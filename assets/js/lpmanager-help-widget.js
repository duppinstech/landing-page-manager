/**
 * Landing Page Manager Help button: toggles the per-screen help panel.
 * No jQuery and no requests; the panel markup is printed by Help_Widget::render().
 *
 * @author Odell Duppins / Duppins Technology
 */
(function () {
    'use strict';

    var toggle = document.querySelector('.lpmanager-help__toggle');
    var panel = document.getElementById('lpmanager-help-panel');
    if (!toggle || !panel) {
        return;
    }
    var closeButton = panel.querySelector('.lpmanager-help__close');
    var sections = panel.querySelectorAll('.lpmanager-help__section');

    /**
     * Settings tabs switch in the browser (Carbon Fields), so show the section
     * whose label matches the selected tab. Other screens have one section.
     */
    function syncSettingsTab() {
        var selected = document.querySelector('.cf-container__tabs-item[aria-selected="true"] button');
        if (!selected || sections.length < 2) {
            return;
        }
        var label = selected.textContent.trim();
        var match = null;
        Array.prototype.forEach.call(sections, function (section) {
            if (section.getAttribute('data-lpmanager-help-tab') === label) {
                match = section;
            }
        });
        if (!match) {
            return;
        }
        Array.prototype.forEach.call(sections, function (section) {
            section.hidden = section !== match;
        });
        var title = match.querySelector('.lpmanager-help__title');
        if (title) {
            panel.setAttribute('aria-labelledby', title.id);
        }
    }

    function isOpen() {
        return !panel.hidden;
    }

    function open() {
        syncSettingsTab();
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        if (closeButton) {
            closeButton.focus();
        }
    }

    function close(returnFocus) {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (returnFocus) {
            toggle.focus();
        }
    }

    toggle.addEventListener('click', function () {
        if (isOpen()) {
            close(false);
        } else {
            open();
        }
    });

    if (closeButton) {
        closeButton.addEventListener('click', function () {
            close(true);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (isOpen() && (event.key === 'Escape' || event.key === 'Esc')) {
            event.preventDefault();
            close(true);
        }
    });

    document.addEventListener('click', function (event) {
        if (isOpen() && event.target.closest && event.target.closest('.cf-container__tabs-item')) {
            window.setTimeout(syncSettingsTab, 0);
        }
    });
})();
