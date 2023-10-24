<?php

namespace Give\PaymentGateways\PayPalCommerce\Banners;

use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;

/**
 * Class PayPalStandardToDonationsMigrationGlobalBanner
 *
 * Note: This class uses notice api to disable the banner for the user.
 *
 * @since 2.33.0
 */
class PayPalStandardToDonationsMigrationGlobalBanner
{
    /**
     * @since 2.33.0
     * @var string
     */
    private $bannerId = 'PayPalStandardToDonationsMigrationGlobalBanner';

    /**
     * @since 2.33.0
     * @return void
     */
    public function setHook()
    {
        // Bailout if user is not can not edit GiveWP settings.
        if (! current_user_can('manage_give_settings')) {
            return;
        }

        add_action('admin_enqueue_scripts', function () {
            $isGivePage = ( isset($_GET['page']) && 'give-forms' === $_GET['page'] )
            || ( isset($_GET['post_type']) && 'give_forms' === $_GET['post_type'] );

            if ($isGivePage && give_is_gateway_active(PayPalStandard::id())) {
                add_action('admin_footer', function () {
                    wp_print_inline_script_tag($this->getModalScript());
                });
            }
        });
    }

    /**
     * Render the banner.
     *
     * @since 2.33.0
     */
    public function getModalScript(): string
    {
        if ($this->isBannerDisabledForUser()) {
            return '';
        }

        $nonce = wp_create_nonce("give_edit_{$this->bannerId}_notice");

        $modalTitle = esc_html__(
            'PayPal Standard Deprecation',
            'give'
        );

        $modalTitle = $this->getIcon() . "&nbsp;&nbsp;$modalTitle";

        $modalSubHeading = esc_html__(
            'PayPal Standard is no longer supported by PayPal',
            'give'
        );

        $modalDescription = esc_html__(
            'Migrate to PayPal Donations, which fully supports PayPal\'s latest API updates. As PayPal Standard is being deprecated, it will soon be removed from our platform. PayPal Standard will continue to work a while longer, but we strongly recommend migrating to PayPal Donations as soon as you can.',
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

        $linkToPayPalDonationsSettingPage = esc_url_raw(
            admin_url(
                'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal'
            )
        );

        return <<<EOT
            const givePayPalStandardToDonationsMigrationGlobalBanner = () => {
                const dismissModalAjaxRequest = async () => {
                    const formData = new FormData();
                    formData.append('give-action', 'dismiss_notices');
                    formData.append('notice_id', '$this->bannerId');
                    formData.append('dismissible_type', 'user');
                    formData.append('dismiss_interval', 'permanent');
                    formData.append('_wpnonce', '$nonce');

                    await fetch(ajaxurl, {
                        method: 'POST',
                        body: formData,
                    })
                };

                new Give.modal.GiveConfirmModal( {
                    classes: {
                        modalWrapper: 'give-paypal-standard-to-donations-migration-banner',
                        cancelBtn: 'give-button--secondary js-has-event-handler',
                    },
                    modalContent: {
                        body: `
                        	<h2 class="give-modal__title">$modalTitle</h2>
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
                                window.open('https://docs.givewp.com/paypal-migration-doc', '_blank')
                            });

                            modal.querySelector('.give-popup-confirm-button').addEventListener('click', async () => {
                                await dismissModalAjaxRequest();
                                window.location.assign('$linkToPayPalDonationsSettingPage');
                            });
                        },
                        close: () => {
                            dismissModalAjaxRequest();
                        }
                    }
                } ).render();
            };

            givePayPalStandardToDonationsMigrationGlobalBanner();
EOT;
    }

    /**
     * @since 2.33.0
     * @return string
     */
    private function getIcon(): string
    {
        return '<svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M8.54163 1.11224C8.19676 0.958893 7.80304 0.958893 7.45817 1.11224C7.19182 1.23068 7.01879 1.43929 6.89839 1.60927C6.78002 1.7764 6.65328 1.99535 6.5139 2.23615L1.00246 11.7559C0.862519 11.9976 0.73535 12.2172 0.649123 12.4036C0.561489 12.593 0.46642 12.8476 0.496719 13.1382C0.535926 13.5143 0.732954 13.856 1.03876 14.0784C1.27509 14.2502 1.54309 14.2955 1.75093 14.3146C1.95543 14.3334 2.2092 14.3333 2.48846 14.3333H13.5113C13.7906 14.3333 14.0444 14.3334 14.2489 14.3146C14.4567 14.2955 14.7247 14.2502 14.961 14.0784C15.2668 13.856 15.4639 13.5143 15.5031 13.1382C15.5334 12.8476 15.4383 12.593 15.3507 12.4036C15.2645 12.2172 15.1373 11.9976 14.9974 11.756L9.48589 2.23612C9.34652 1.99534 9.21978 1.77639 9.10141 1.60927C8.98101 1.43929 8.80798 1.23068 8.54163 1.11224ZM8.66659 6C8.66659 5.63181 8.36811 5.33333 7.99992 5.33333C7.63173 5.33333 7.33325 5.63181 7.33325 6V8.66666C7.33325 9.03485 7.63173 9.33333 7.99992 9.33333C8.36811 9.33333 8.66659 9.03485 8.66659 8.66666V6ZM7.99992 10.6667C7.63173 10.6667 7.33325 10.9651 7.33325 11.3333C7.33325 11.7015 7.63173 12 7.99992 12H8.00659C8.37478 12 8.67325 11.7015 8.67325 11.3333C8.67325 10.9651 8.37478 10.6667 8.00659 10.6667H7.99992Z" fill="#F29718"/>
</svg>';
    }

    /**
     * @since 2.33.0
     * @return bool
     */
    private function isBannerDisabledForUser(): bool
    {
        $optionKey = give()->notices->get_notice_key($this->bannerId, 'permanent', get_current_user_id());
        $optionData = \Give_Cache::get($optionKey, true);

        return ! empty($optionData) && ! is_wp_error($optionData);
    }
}
