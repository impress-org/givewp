/**
 * Webhook Status field of the Stripe Manage Accounts page, and the modal that lists the webhook endpoints of an account.
 *
 * @since TBD
 */
import { __, sprintf } from '@wordpress/i18n';

const MANAGE_ACTION = 'givewp_stripe_manage_webhook_endpoints';

/**
 * What the status button says for each state the server reports. "setup" states start the webhook setup for the
 * account; the others read the status again.
 */
const getStates = () => ({
    loading: { label: __('Checking…', 'give'), setup: false, disabled: true },
    healthy: { label: __('Active', 'give'), setup: false, disabled: false },
    missing: { label: __('Set up webhook', 'give'), setup: true, disabled: false },
    disabled: { label: __('Enable webhook', 'give'), setup: true, disabled: false },
    missing_events: { label: __('Add missing events', 'give'), setup: true, disabled: false },
    error: { label: __('Check again', 'give'), setup: false, disabled: false },
    unavailable: { label: __('No key for this mode', 'give'), setup: false, disabled: true },
});

/**
 * What the busy notice says while a modal operation runs.
 */
const getBusyMessages = () => ({
    update: __('Saving changes…', 'give'),
    create: __('Adding endpoint…', 'give'),
    delete: __('Deleting endpoint…', 'give'),
});

/**
 * The Webhook Status field of the account the open modal belongs to.
 */
let openField = null;

/**
 * @since TBD
 *
 * @param {string} text Plain text.
 *
 * @return {string} The text as HTML.
 */
function escapeHtml(text) {
    const entities = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };

    return text.replace(/[&<>"']/g, (character) => entities[character]);
}

/**
 * @since TBD
 *
 * @param {HTMLElement} field The Webhook Status field of an account.
 * @param {string}      state One of the keys of getStates().
 */
function setState(field, state) {
    const states = getStates();
    const config = states[state] || states.error;
    const button = field.querySelector('.give-stripe-webhook-status-button');

    field.setAttribute('data-state', state);
    button.textContent = config.label;
    button.disabled = config.disabled;
    button.classList.toggle('give-stripe-webhook-setup-button', config.setup);
    button.setAttribute('data-nonce', field.getAttribute('data-ensure-nonce'));
    button.setAttribute('data-account', field.getAttribute('data-account'));

    field
        .querySelector('.give-stripe-webhook-edit')
        .classList.toggle('give-hidden', 'loading' === state || 'unavailable' === state);
}

/**
 * @since TBD
 *
 * @param {string}   operation One of the operations of the manage controller.
 * @param {string}   nonce     Nonce for the manage controller.
 * @param {FormData} formData  Extra fields to send.
 *
 * @return {Promise<object>} The "data" of the response.
 */
function post(operation, nonce, formData = new FormData()) {
    formData.append('action', MANAGE_ACTION);
    formData.append('operation', operation);
    formData.append('_ajax_nonce', nonce);

    return fetch(window.ajaxurl, { method: 'POST', credentials: 'same-origin', body: formData })
        .then((response) => response.json())
        .then((response) => {
            if (!response.success) {
                throw new Error('Webhook request failed.');
            }

            return response.data;
        });
}

/**
 * How many status refreshes have started. A response that belongs to an older refresh is dropped, so a
 * slow first read cannot overwrite the state a newer read already applied.
 *
 * @since TBD
 */
let statusRefreshCount = 0;

/**
 * Reads the webhook state of every account with one request.
 *
 * @since TBD
 */
function refreshStatuses() {
    const fields = Array.from(document.querySelectorAll('.give-stripe-webhook-status'));

    if (!fields.length) {
        return;
    }

    const refresh = ++statusRefreshCount;

    fields.forEach((field) => setState(field, 'loading'));

    post('status', fields[0].getAttribute('data-manage-nonce'))
        .then(({ states }) => {
            if (refresh !== statusRefreshCount) {
                return;
            }

            fields.forEach((field) => setState(field, states[field.getAttribute('data-account')] || 'unavailable'));
        })
        .catch(() => {
            if (refresh !== statusRefreshCount) {
                return;
            }

            fields.forEach((field) => setState(field, 'error'));
        });
}

/**
 * The loader shown while the modal fetches the endpoint list.
 *
 * @since TBD
 *
 * @return {string} The loader as HTML.
 */
function loaderMarkup() {
    return Give.fn.loader({}, { show: null, loadingText: Give.fn.getGlobalVar('loader_translation').processing });
}

/**
 * Replaces the content of the open modal. The markup comes from WebhookEndpointsModalView, which escapes every value.
 *
 * @since TBD
 *
 * @param {string} html Content of the modal body.
 *
 * @return {HTMLElement|null} The container that now holds the content.
 */
function showContent(html) {
    const container = document.querySelector('.give-stripe-webhooks-content');

    if (container) {
        container.replaceChildren(document.createRange().createContextualFragment(html));
    }

    return container;
}

/**
 * Removes every notice from the modal content. Banners and hints are not notices, so they stay.
 *
 * @since TBD
 *
 * @param {HTMLElement} container The modal content container.
 */
function removeNotices(container) {
    container.querySelectorAll('.give-stripe-webhooks__notice').forEach((notice) => notice.remove());
}

/**
 * Shows a busy notice while an operation runs. The notice announces itself to screen readers through role="status".
 *
 * @since TBD
 *
 * @param {string} operation "create", "update" or "delete".
 */
function showBusyNotice(operation) {
    const container = document.querySelector('.give-stripe-webhooks-content');
    const message = getBusyMessages()[operation];

    if (!container || !message) {
        return;
    }

    removeNotices(container);

    const notice = document.createElement('div');
    notice.className = 'give-stripe-webhooks__notice give-stripe-webhooks__notice--busy';
    notice.setAttribute('role', 'status');
    notice.textContent = message;
    container.prepend(notice);
}

/**
 * Locks or unlocks the modal content while an operation runs: every button is disabled, so a second
 * operation cannot start before the first one answers.
 *
 * @since TBD
 *
 * @param {boolean} busy Whether an operation is running.
 */
function setBusy(busy) {
    const container = document.querySelector('.give-stripe-webhooks-content');

    if (!container) {
        return;
    }

    container.classList.toggle('is-busy', busy);

    if (busy) {
        container.setAttribute('aria-busy', 'true');
    } else {
        container.removeAttribute('aria-busy');
    }

    container.querySelectorAll('button').forEach((button) => (button.disabled = busy));
}

/**
 * Marks one form as busy with a spinner on its submit button.
 *
 * @since TBD
 *
 * @param {HTMLElement|null} form The form that started the operation.
 * @param {boolean}          busy Whether an operation is running.
 */
function setSubmitBusy(form, busy) {
    if (!form) {
        return;
    }

    form.setAttribute('aria-busy', busy ? 'true' : 'false');

    const submit = form.querySelector('button[type="submit"]:not(.give-hidden)');

    if (submit) {
        submit.classList.toggle('is-busy', busy);
    }
}

/**
 * Puts the keyboard focus on the notice of the modal content, so keyboard and screen reader users
 * land on the result of the last operation.
 *
 * @since TBD
 *
 * @param {HTMLElement|null} container The modal content container.
 */
function focusNotice(container) {
    if (!container) {
        return;
    }

    const notice = container.querySelector('.give-stripe-webhooks__notice');

    if (!notice) {
        return;
    }

    notice.setAttribute('tabindex', '-1');
    notice.focus({ preventScroll: true });
}

/**
 * Opens the form an operation came from, so the admin sees it again after the content was refreshed.
 * A failed update shows the form of its endpoint; a failed create shows the Add endpoint form.
 *
 * @since TBD
 *
 * @param {string}      operation "create", "update" or "delete".
 * @param {FormData}    formData  The fields the form sent.
 * @param {HTMLElement} container The refreshed modal content.
 *
 * @return {HTMLElement|null} The reopened form, or null when there is none to reopen.
 */
function reopenForm(operation, formData, container) {
    if (!container) {
        return null;
    }

    if ('update' === operation) {
        const endpointId = formData.get('endpoint_id');
        const input = Array.from(
            container.querySelectorAll('.give-stripe-webhooks__edit input[name="endpoint_id"]')
        ).find((candidate) => candidate.value === endpointId);

        if (!input) {
            return null;
        }

        const details = input.closest('details');
        details.open = true;

        return details.querySelector('form');
    }

    if ('create' === operation) {
        const details = container.querySelector('details.give-stripe-webhooks__create');

        if (details) {
            details.open = true;

            return details.querySelector('form');
        }

        return container.querySelector('.give-stripe-webhooks__create form');
    }

    return null;
}

/**
 * Puts the values the admin submitted back into a form. The refreshed markup holds the values Stripe
 * has stored, which are not what a failed operation tried to save. The events of an endpoint that
 * sends every event are left alone, because its checkboxes are disabled and Stripe keeps them.
 *
 * @since TBD
 *
 * @param {HTMLElement|null} form     The form to fill.
 * @param {FormData}         formData The fields the admin submitted.
 */
function restoreFormValues(form, formData) {
    if (!form) {
        return;
    }

    const url = formData.get('url');
    const input = form.querySelector('input[name="url"]');

    if (null !== url && input) {
        input.value = url;
    }

    const status = formData.get('status');
    const select = form.querySelector('select[name="status"]');

    if (null !== status && select) {
        select.value = status;
    }

    const fieldset = form.querySelector('.give-stripe-webhooks__events');

    if (fieldset && !fieldset.disabled) {
        const events = formData.getAll('events[]');

        fieldset.querySelectorAll('input[name="events[]"]').forEach((checkbox) => {
            checkbox.checked = events.includes(checkbox.value);
        });
    }
}

/**
 * Shows the failure of the first load of the modal: an error notice and a button that reads the list again.
 *
 * @since TBD
 */
function showListFailed() {
    const container = document.querySelector('.give-stripe-webhooks-content');

    if (!container) {
        return;
    }

    const notice = document.createElement('div');
    notice.className = 'give-stripe-webhooks__notice give-stripe-webhooks__notice--error';
    notice.setAttribute('role', 'status');
    notice.textContent = __('The request failed. Check your connection and try again.', 'give');

    const retry = document.createElement('button');
    retry.type = 'button';
    retry.className = 'button give-stripe-webhooks__retry';
    retry.textContent = __('Try again', 'give');
    retry.addEventListener('click', () => {
        showContent(loaderMarkup());
        runOperation('list');
    });

    container.replaceChildren(notice, retry);

    notice.setAttribute('tabindex', '-1');
    notice.focus({ preventScroll: true });
}

/**
 * Shows the failure of an operation without wiping the modal: the notice is prepended, the form is
 * unlocked and filled with the values the admin submitted, and the form is opened again.
 *
 * @since TBD
 *
 * @param {HTMLElement} form      The form that started the operation.
 * @param {FormData}    formData  The fields the admin submitted.
 * @param {string}      operation "create", "update" or "delete".
 */
function showOperationError(form, formData, operation) {
    const container = document.querySelector('.give-stripe-webhooks-content');

    if (!container) {
        return;
    }

    const notice = document.createElement('div');
    notice.className = 'give-stripe-webhooks__notice give-stripe-webhooks__notice--error';
    notice.setAttribute('role', 'status');
    notice.textContent = __('The request failed. Check your connection and try again.', 'give');

    removeNotices(container);
    container.prepend(notice);

    const reopened = reopenForm(operation, formData, container);
    restoreFormValues(reopened || form, formData);
    focusNotice(container);
}

/**
 * Sends the operation of a modal form, then shows the refreshed list and updates the status button.
 * A failed request keeps the list and the form values; the error path decides where the notice goes.
 *
 * @since TBD
 *
 * @param {string}        operation "list", "create", "update" or "delete".
 * @param {FormData}      formData  Form fields.
 * @param {HTMLElement|null} form   The form that started the operation; null for the list.
 */
function runOperation(operation, formData = new FormData(), form = null) {
    const field = openField;

    if (!field) {
        return Promise.resolve();
    }

    formData.append('account', field.getAttribute('data-account'));

    return post(operation, field.getAttribute('data-manage-nonce'), formData)
        .then(({ html, state }) => {
            const container = showContent(html);
            setState(field, state);
            setBusy(false);

            const failedNotice = container && container.querySelector('.give-stripe-webhooks__notice--error');

            if ('update' === operation) {
                /* The admin sees the saved values of the endpoint they edited, on success and on failure. */
                const reopened = reopenForm(operation, formData, container);

                if (failedNotice) {
                    restoreFormValues(reopened, formData);
                }
            } else if (failedNotice && 'create' === operation) {
                const reopened = reopenForm(operation, formData, container);
                restoreFormValues(reopened, formData);
            }

            focusNotice(container);
        })
        .catch(() => {
            setBusy(false);
            setSubmitBusy(form, false);

            if (!form) {
                showListFailed();

                return;
            }

            showOperationError(form, formData, operation);
        });
}

/**
 * @since TBD
 *
 * @param {HTMLElement} field The Webhook Status field of an account.
 */
function openModal(field) {
    openField = field;

    new Give.modal.GiveNoticeAlert({
        mainClass: 'give-stripe-webhook-modal',
        modalContent: {
            title: escapeHtml(sprintf(__('Webhooks for %s', 'give'), field.getAttribute('data-account-name'))),
            body: `<div class="give-stripe-webhooks-content">${loaderMarkup()}</div>`,
        },
        callbacks: {
            close: () => {
                openField = null;
            },
        },
    }).render();

    runOperation('list');
}

/**
 * Swaps the Delete button of an endpoint for a confirm row with a Delete and a Cancel button.
 *
 * @since TBD
 *
 * @param {HTMLElement} form The delete form of the endpoint.
 */
function enterDeleteConfirm(form) {
    const row = document.createElement('span');
    row.className = 'give-stripe-webhooks__delete-confirm';

    const label = document.createElement('span');
    label.className = 'give-stripe-webhooks__delete-confirm-label';
    label.textContent = form.getAttribute('data-confirm');

    const confirmButton = document.createElement('button');
    confirmButton.type = 'submit';
    confirmButton.className = 'give-stripe-webhooks__delete-confirm-button';
    confirmButton.textContent = __('Delete', 'give');

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.className = 'give-stripe-webhooks__delete-cancel-button';
    cancelButton.textContent = __('Cancel', 'give');
    cancelButton.addEventListener('click', () => exitDeleteConfirm(form));

    row.append(label, confirmButton, cancelButton);

    form.classList.add('is-confirming');
    form.querySelector('button[type="submit"]').classList.add('give-hidden');
    form.append(row);
    cancelButton.focus();
}

/**
 * Puts the Delete button of an endpoint back and removes the confirm row.
 *
 * @since TBD
 *
 * @param {HTMLElement} form The delete form of the endpoint.
 */
function exitDeleteConfirm(form) {
    const row = form.querySelector('.give-stripe-webhooks__delete-confirm');

    if (row) {
        row.remove();
    }

    form.classList.remove('is-confirming');
    form.querySelector('button[type="submit"]').classList.remove('give-hidden');
}

window.addEventListener('DOMContentLoaded', function () {
    refreshStatuses();

    document.addEventListener('click', (event) => {
        const confirming = document.querySelector('.give-stripe-webhooks__delete.is-confirming');

        if (confirming && !event.target.closest('.give-stripe-webhooks__delete')) {
            exitDeleteConfirm(confirming);
        }

        const edit = event.target.closest('.give-stripe-webhook-edit-btn');

        if (edit) {
            event.preventDefault();
            openModal(edit.closest('.give-stripe-webhook-status'));

            return;
        }

        const button = event.target.closest('.give-stripe-webhook-status-button');

        /* The buttons that set up the webhook are handled by stripe-admin.js. */
        if (button && !button.classList.contains('give-stripe-webhook-setup-button')) {
            refreshStatuses();
        }
    });

    document.addEventListener('keydown', (event) => {
        if ('Escape' !== event.key) {
            return;
        }

        const confirming = document.querySelector('.give-stripe-webhooks__delete.is-confirming');

        if (confirming) {
            exitDeleteConfirm(confirming);
        }
    });

    /* The toggle event does not bubble, so the accordion listens in the capture phase. */
    document.addEventListener(
        'toggle',
        (event) => {
            const details = event.target;

            if (!details.matches('.give-stripe-webhooks__edit, details.give-stripe-webhooks__create')) {
                return;
            }

            if (!details.open || !openField) {
                return;
            }

            details
                .closest('.give-stripe-webhooks')
                .querySelectorAll('details[open]')
                .forEach((other) => {
                    if (other !== details) {
                        other.open = false;
                    }
                });
        },
        true
    );

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.give-stripe-webhooks form[data-webhook-operation]');

        if (!form || !openField) {
            return;
        }

        event.preventDefault();

        const operation = form.getAttribute('data-webhook-operation');

        if ('delete' === operation && !form.classList.contains('is-confirming')) {
            enterDeleteConfirm(form);

            return;
        }

        const formData = new FormData(form);

        setBusy(true);
        setSubmitBusy(form, true);
        showBusyNotice(operation);

        runOperation(operation, formData, form);
    });

    /* stripe-admin.js dispatches this when a webhook setup request ends. */
    window.addEventListener('givewp-stripe-webhook-setup-finished', refreshStatuses);
});
