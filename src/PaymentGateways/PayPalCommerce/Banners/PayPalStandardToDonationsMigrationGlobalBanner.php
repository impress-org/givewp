<?php

namespace Give\PaymentGateways\PayPalCommerce\Banners;

use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;

/**
 * Class PayPalStandardToDonationsMigrationGlobalBanner
 *
 * @unreleased
 */
class PayPalStandardToDonationsMigrationGlobalBanner
{

    /**
     * @unreleased
     * @return void
     */
    public function setHook()
    {

        add_action('admin_enqueue_scripts', function () {
            if (give_is_gateway_active(PayPalStandard::id())) {
                wp_add_inline_script('give-admin-scripts', $this->getModalScript());
            }
        });
    }

    /**
     * Render the banner.
     *
     * @unreleased
     */
    public function getModalScript(): string
    {
        $modalTitle = esc_html__(
            'PayPal Standard Deprecation',
            'give'
        );

        $modalSubHeading = esc_html__(
            'Paypal Standard is no longer supported by PayPal',
            'give'
        );

        $modalDescription = esc_html__(
            'Migrate to PayPal Donations, which fully supports PayPal\'s latest API updates. As PayPal Standard is being deprecated, it will soon be removed from our platform.',
            'give'
        );

        $modalCancelButtonTitle = esc_html__(
            'Read Documentation',
            'give'
        );

        $modalConfirmButtonTitle = esc_html__(
            'Connect PayPal Donations',
            'give'
        );

        $linkToPayPalDonationsSettingPage = esc_url_raw(admin_url(
            'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal'
        ));

        return <<<EOT
            document.addEventListener("DOMContentLoaded", () => {
                new Give.modal.GiveConfirmModal( {
                    classes: {
                        modalWrapper: 'give-paypal-standard-to-donations-migration-banner',
                        cancelBtn: 'give-button--secondary js-has-event-handler',
                    },
                    modalContent: {
                        title: '$modalTitle',
                        body: `
                            <div class="give-modal__description">
                                <strong>$modalSubHeading</strong>
                                <p>$modalDescription</p>
                            </div>
                        `.trim(),
                        cancelBtnTitle: '$modalCancelButtonTitle',
                        confirmBtnTitle: '$modalConfirmButtonTitle'
                    },
                    closeOnBgClick: true,
                    showCloseBtn: true,
                    callbacks: {
                    	open: () => {
							const modal = document.querySelector('.give-modal');

							modal.querySelector('.give-popup-close-button').addEventListener('click', () => {
								window.open('https://google.com', '_blank')
							});

							modal.querySelector('.give-popup-confirm-button').addEventListener('click', () => {
								window.location.assign('$linkToPayPalDonationsSettingPage');
							});
						}
					}
                } ).render();
            });
EOT;
    }
}
