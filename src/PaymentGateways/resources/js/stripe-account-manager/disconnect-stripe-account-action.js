/**
 * Disconnect stripe account
 *
 * This will be used to disconnect any Stripe account from the list
 *
 * @since TBD Show a warning before the reload when the webhook endpoint could not be deleted. Reload when the request fails.
 * @since 2.13.0
 */
import { __ } from '@wordpress/i18n'

window.addEventListener('DOMContentLoaded', function () {
    const disconnectBtns = Array.from(document.querySelectorAll('.give-stripe-disconnect-account-btn'));

    if (!disconnectBtns.length) {
        return;
    }

    disconnectBtns.forEach((button) => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const button = e.target;
            let modalTitle = __('Disconnect Stripe Account', 'give');
            let modalMessage = __('Are you sure you want to disconnect this Stripe account?', 'give');

            new Give.modal.GiveConfirmModal({
                type: 'alert',
                classes: {
                    modalWrapper: 'give-modal--warning',
                },
                modalContent: {
                    title: modalTitle,
                    desc: modalMessage,
                },
                successConfirm: () => {
                    fetch(button.getAttribute('href'))
                        .then((response) => response.json())
                        .then((response) => {
                            if (!response.success) {
                                return;
                            }

                            const warning = response.data && response.data.webhookWarning;

                            if (!warning) {
                                window.location.reload();

                                return;
                            }

                            const desc = document.createElement('span');
                            desc.textContent = warning;

                            new Give.modal.GiveWarningAlert({
                                modalContent: {
                                    title: __('Stripe account disconnected', 'give'),
                                    desc: desc.outerHTML,
                                },
                                callbacks: {
                                    close: () => window.location.reload(),
                                },
                            }).render();
                        })
                        /* A failed request may come after the account was removed, so the reload shows its real state. */
                        .catch(() => window.location.reload());
                },
            }).render();
        });
    });
});
