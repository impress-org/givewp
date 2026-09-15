<?php

namespace Give\DonationForms\Blocks\DonationFormBlock\Controllers;

use Give\DonationForms\Actions\GenerateDonationConfirmationReceiptViewRouteUrl;
use Give\DonationForms\Actions\GenerateDonationFormViewRouteUrl;
use Give\DonationForms\Blocks\DonationFormBlock\DataTransferObjects\BlockAttributes;
use Give\DonationForms\DataTransferObjects\DonationConfirmationReceiptViewRouteData;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Framework\Blocks\BlockModel;
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
     * @since TBD emit the form's shape so the embed can hold a skeleton of it while loading.
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
        $embedShape = wp_json_encode($this->getEmbedShape($donationForm));

        /**
         * Note: iframe-resizer uses querySelectorAll so using a data attribute makes the most sense to target.
         * It will also generate a dynamic ID - so when we have multiple embeds on a page there will be no conflict.
         */
        return sprintf(
            "<div class='root-data-givewp-embed' data-form-locale='%s' data-form-url='%s' data-form-view-url='%s' data-src='%s' data-givewp-embed-id='%s' data-form-format='%s' data-open-form-button='%s' data-embed-shape='%s' style='--givewp-primary-color: %s; --givewp-secondary-color: %s;'></div>",
            esc_attr($locale),
            esc_attr($formUrl),
            esc_attr($formViewUrl),
            esc_attr($viewUrl),
            esc_attr($embedId),
            esc_attr($blockAttributes->formFormat),
            esc_attr($blockAttributes->openFormButton),
            esc_attr($embedShape),
            esc_attr($colorSettings['primaryColor']),
            esc_attr($colorSettings['secondaryColor'])
        );
    }

    /**
     * The little the embed needs to sketch the form before it loads: the design, whether there is a
     * header and the two header parts with real height (goal bar, image), the block names in each
     * section and how many gateway rows the donor will see. Every value is already on the form or in
     * options, so this costs no extra queries. The embed only knows how to draw the core designs and
     * falls back to a spinner for any other design id.
     *
     * @since TBD
     */
    protected function getEmbedShape(DonationForm $donationForm): array
    {
        $settings = $donationForm->settings;

        $sections = array_map(static function (BlockModel $block): array {
            if ($block->name !== 'givewp/section' || !$block->innerBlocks) {
                return [$block->name];
            }

            return array_map(static function (BlockModel $innerBlock): string {
                return $innerBlock->name;
            }, $block->innerBlocks->getBlocks());
        }, $donationForm->blocks->getBlocks());

        return [
            'design' => (string)$settings->designId,
            'header' => (bool)$settings->showHeader,
            'goal' => (bool)$settings->enableDonationGoal,
            'image' => (bool)$settings->designSettingsImageUrl && $settings->designSettingsImageStyle !== 'background',
            'sections' => array_values($sections),
            'gateways' => count(give(DonationFormRepository::class)->getEnabledPaymentGateways($donationForm->id)),
        ];
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
