<?php

namespace Give\DonationForms\Blocks\DonationFormBlock\Controllers;

use Give\DonationForms\Actions\GenerateDonationConfirmationReceiptViewRouteUrl;
use Give\DonationForms\Actions\GenerateDonationFormViewRouteUrl;
use Give\DonationForms\Blocks\DonationFormBlock\DataTransferObjects\BlockAttributes;
use Give\DonationForms\DataTransferObjects\DonationConfirmationReceiptViewRouteData;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\EnqueueScript;
use Give\Framework\Routes\RouteListener;
use Give\Helpers\Language;

class BlockRenderController
{
    /**
     * @since 4.1.0
     */
    protected static int $embedInstance = 0;

    /**
	 * @since 4.14.5 add escaping to the output.
     * @since 4.7.0 detach check for gutenberg editor to make this more reusable
     * @since 4.1.0 updated with embed ID instance fallback when block ID is not set.
     * @since 3.22.0 Add locale support
     * @since 3.2.0 include form url for new tab format.
     * @since 3.0.0
     *
     * @return string|null
     */
    public function render(array $attributes)
    {
        static::$embedInstance++;

        $blockAttributes = BlockAttributes::fromArray($attributes);

        if (!$blockAttributes->formId) {
            return null;
        }

        $this->loadEmbedScript();

        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::find($blockAttributes->formId);

        $embedId = $blockAttributes->blockId ?? 'givewp-embed-' . static::$embedInstance;

        $locale = Language::getLocale();
        $viewUrl = $this->getViewUrl($donationForm, $embedId);
        $formUrl = add_query_arg(['p' => $blockAttributes->formId], site_url('?post_type=give_forms'));
        $formViewUrl = $this->getFormViewUrl($donationForm);
        $colorSettings = $donationForm->getColorSettings();

        /**
         * Note: iframe-resizer uses querySelectorAll so using a data attribute makes the most sense to target.
         * It will also generate a dynamic ID - so when we have multiple embeds on a page there will be no conflict.
         */
        return sprintf(
            "<div class='root-data-givewp-embed' data-form-locale='%s' data-form-url='%s' data-form-view-url='%s' data-src='%s' data-givewp-embed-id='%s' data-form-format='%s' data-open-form-button='%s' style='--givewp-primary-color: %s; --givewp-secondary-color: %s;'></div>",
            esc_attr($locale),
            esc_attr($formUrl),
            esc_attr($formViewUrl),
            esc_attr($viewUrl),
            esc_attr($embedId),
            esc_attr($blockAttributes->formFormat),
            esc_attr($blockAttributes->openFormButton),
            esc_attr($colorSettings['primaryColor']),
            esc_attr($colorSettings['secondaryColor'])
        );
    }

    /**
     * Return early if we're still inside the editor to avoid server side effects
     *
     * @since 4.7.0
     *
     * @return boolean
     */
    public function isGutenbergEditor(): bool
    {
        return !empty($_REQUEST['post']) || !empty($_REQUEST['action']) || !empty($_REQUEST['_locale']);
    }

    /**
     * If the page loads with our receipt route listener args then we need to render the receipt.
     *
     * @since 3.0.0
     */
    protected function shouldDisplayDonationConfirmationReceipt(string $embedId): bool
    {
        $routeListener = new RouteListener(
            'donation-completed',
            'show-donation-confirmation-receipt'
        );

        return $routeListener->isValid($_GET, function ($request) use ($embedId) {
            $isset = isset($request['givewp-embed-id'], $request['givewp-receipt-id']);

            return $isset && $request['givewp-embed-id'] === $embedId && DonationConfirmationReceiptViewRouteData::isReceiptIdValid(
                    $request['givewp-receipt-id']
                );
        });
    }

    /**
     * Get the iframe URL.
     * This could either be the donation form view or the donation confirmation receipt view.
     *
     * @since 3.0.0
     */
    private function getViewUrl(DonationForm $donationForm, string $embedId): string
    {
        if ($this->shouldDisplayDonationConfirmationReceipt($embedId)) {
            $receiptId = give_clean($_GET['givewp-receipt-id']);

            return (new GenerateDonationConfirmationReceiptViewRouteUrl())($receiptId);
        }

        return $this->getFormViewUrl($donationForm);
    }

    /**
     * @since 3.4.0
     */
    private function getFormViewUrl(DonationForm $donationForm): string
    {
        return (new GenerateDonationFormViewRouteUrl())($donationForm->id);
    }

    /**
     *
     * Load embed givewp script to resize iframe
     * @see        https://github.com/davidjbradshaw/iframe-resizer
     *
     * @since 3.0.0
     */
    protected function loadEmbedScript()
    {
        (new EnqueueScript(
            'givewp-donation-form-embed-app',
            'build/donationFormBlockApp.js',
            GIVE_PLUGIN_DIR,
            GIVE_PLUGIN_URL,
            'give'
        ))
            ->dependencies(['jquery'])
            ->loadInFooter()
            ->enqueue();

        wp_enqueue_style(
            'givewp-donation-form-embed-app',
            GIVE_PLUGIN_URL . 'build/donationFormBlockApp.css'
        );
    }
}
