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
 * Reads the webhook state of every account with one request.
 *
 * @since TBD
 */
function refreshStatuses() {
    const fields = Array.from(document.querySelectorAll('.give-stripe-webhook-status'));

    if (!fields.length) {
        return;
    }

    fields.forEach((field) => setState(field, 'loading'));

    post('status', fields[0].getAttribute('data-manage-nonce'))
        .then(({ states }) => {
            fields.forEach((field) => setState(field, states[field.getAttribute('data-account')] || 'unavailable'));
        })
        .catch(() => fields.forEach((field) => setState(field, 'error')));
}

/**
 * Replaces the content of the open modal. The markup comes from WebhookEndpointsModalView, which escapes every value.
 *
 * @since TBD
 *
 * @param {string} html Content of the modal body.
 */
function showContent(html) {
    const container = document.querySelector('.give-stripe-webhooks-content');

    if (container) {
        container.replaceChildren(document.createRange().createContextualFragment(html));
    }
}

/**
 * @since TBD
 */
function showRequestFailed() {
    const container = document.querySelector('.give-stripe-webhooks-content');

    if (!container) {
        return;
    }

    const notice = document.createElement('div');
    notice.className = 'give-stripe-webhooks__notice give-stripe-webhooks__notice--error';
    notice.textContent = __('The request failed. Reload the page and try again.', 'give');
    container.replaceChildren(notice);
}

/**
 * Sends the operation of a modal form, then shows the refreshed list and updates the status button.
 *
 * @since TBD
 *
 * @param {string}   operation "list", "create", "update" or "delete".
 * @param {FormData} formData  Form fields.
 */
function runOperation(operation, formData = new FormData()) {
    const field = openField;

    formData.append('account', field.getAttribute('data-account'));

    return post(operation, field.getAttribute('data-manage-nonce'), formData)
        .then(({ html, state }) => {
            showContent(html);
            setState(field, state);
        })
        .catch(showRequestFailed);
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
            body: `<div class="give-stripe-webhooks-content">${Give.fn.loader(
                {},
                { show: null, loadingText: Give.fn.getGlobalVar('loader_translation').processing }
            )}</div>`,
        },
        callbacks: {
            close: () => {
                openField = null;
            },
        },
    }).render();

    runOperation('list');
}

window.addEventListener('DOMContentLoaded', function () {
    refreshStatuses();

    document.addEventListener('click', (event) => {
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

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.give-stripe-webhooks form[data-webhook-operation]');

        if (!form || !openField) {
            return;
        }

        event.preventDefault();

        const confirmation = form.getAttribute('data-confirm');

        if (confirmation && !window.confirm(confirmation)) {
            return;
        }

        const formData = new FormData(form);
        form.querySelectorAll('button').forEach((button) => (button.disabled = true));

        runOperation(form.getAttribute('data-webhook-operation'), formData);
    });

    /* stripe-admin.js dispatches this when a webhook setup request ends. */
    window.addEventListener('givewp-stripe-webhook-setup-finished', refreshStatuses);
});
