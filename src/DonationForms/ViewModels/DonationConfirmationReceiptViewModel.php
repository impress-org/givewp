<?php

namespace Give\DonationForms\ViewModels;

use Give\DonationForms\FormDesigns\ClassicFormDesign\ClassicFormDesign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Donations\Models\Donation;
use Give\Framework\DesignSystem\Actions\RegisterDesignSystemStyles;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Receipts\DonationReceiptBuilder;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\Helpers\Language;

/**
 * @since 3.0.0
 */
class DonationConfirmationReceiptViewModel
{
    use HasScriptAssetFile;

    /**
     * @var Donation
     */
    public $donation;

    /**
     * @since 3.0.0
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    /**
     * @since 3.0.0
     */
    public function getDonationForm(): DonationForm
    {
        return DonationForm::find($this->donation->formId);
    }

    /**
     * @since 3.0.0
     */
    public function getReceipt(): DonationReceipt
    {
        $receipt = new DonationReceipt($this->donation);

        return (new DonationReceiptBuilder($receipt))->toConfirmationPage();
    }

    /**
     * @since 3.0.0
     */
    public function exports(): array
    {
        return [
            'receipt' => $this->getReceipt()->toArray()
        ];
    }

    /**
     * @since 3.0.0
     */
    public function formExports(): array
    {
        /** @var DonationFormRepository $donationFormRepository */
        $donationFormRepository = give(DonationFormRepository::class);

        $formDataGateways = $donationFormRepository->getFormDataGateways($this->donation->formId);
        $formApi = !$donationFormRepository->isLegacyForm(
            $this->donation->formId
        ) ? $donationFormRepository->getFormSchemaFromBlocks(
            $this->donation->formId,
            $this->getDonationForm()->blocks
        )->jsonSerialize() : null;

        return [
            'registeredGateways' => $formDataGateways,
            'form' => $formApi,
        ];
    }

    /**
     * @since 3.11.0 Sanitize customCSS property
     * @since 3.0.0
     */
    public function render(): string
    {
        /** @var DonationFormRepository $donationFormRepository */
        $donationFormRepository = give(DonationFormRepository::class);

        $donationForm = !$donationFormRepository->isLegacyForm(
            $this->donation->formId
        ) ? $this->getDonationForm() : null;

        $formDesignId = $donationForm ? $donationForm->settings->designId : ClassicFormDesign::id();
        $customCss = $donationForm && $donationForm->settings->customCss ? $donationForm->settings->customCss : null;
        $primaryColor = $donationForm ? $donationForm->settings->primaryColor : '#69B868';
        $secondaryColor = $donationForm ? $donationForm->settings->secondaryColor : '#000000';

        $this->enqueueGlobalStyles($primaryColor, $secondaryColor);

        $this->enqueueFormScripts($formDesignId);

        ob_start();
        wp_print_styles();
        wp_print_head_scripts();
        ?>

        <?php
        if ($customCss): ?>
            <style><?php echo wp_strip_all_tags($customCss); ?></style>
        <?php
        endif; ?>

        <div data-theme="light" id="root-givewp-donation-confirmation-receipt"
             data-iframe-height
             class="givewp-donation-confirmation-receipt"
             style="
                     --givewp-primary-color:<?= $primaryColor ?>;
                     --givewp-secondary-color:<?= $secondaryColor ?>;
                     "
        ></div>

        <?php
        wp_print_footer_scripts();

        echo ob_get_clean();

        exit();
    }

    /**
     * @since 3.0.0
     */
    public function enqueueGlobalStyles(string $primaryColor, string $secondaryColor)
    {
        (new RegisterDesignSystemStyles())();
        wp_enqueue_style('givewp-design-system-foundation');

        wp_register_style(
            'givewp-base-form-styles',
            GIVE_PLUGIN_URL . 'build/baseFormDesignCss.css'
        );

        wp_add_inline_style(
            'givewp-base-form-styles',
            ":root {
            --givewp-primary-color:{$primaryColor};
            --givewp-secondary-color:{$secondaryColor};
            }"
        );

        wp_enqueue_style('givewp-base-form-styles');
    }

    /**
     * Loads scripts in order: [Registrars, Designs, App]
     *
     * @since 3.0.0
     *
     * @return void
     */
    private function enqueueFormScripts(?string $formDesignId)
    {
        $handle = 'givewp-donation-form-registrars';
        wp_enqueue_script(
            $handle,
            GIVE_PLUGIN_URL . 'build/donationFormRegistrars.js',
            $this->getScriptAssetDependencies(GIVE_PLUGIN_DIR . 'build/donationFormRegistrars.asset.php'),
            GIVE_VERSION,
            true
        );

        Language::setScriptTranslations($handle);

        wp_add_inline_script(
            'givewp-donation-form-registrars',
            'window.givewpDonationFormExports = ' . wp_json_encode($this->formExports()) . ';',
            'before'
        );

        wp_add_inline_script(
            'givewp-donation-form-registrars',
            'window.givewpDonationConfirmationReceiptExports = ' . wp_json_encode($this->exports()) . ';',
            'before'
        );

        // load template
        /** @var FormDesignRegistrar $formDesignRegistrar */
        $formDesignRegistrar = give(FormDesignRegistrar::class);

        // silently fail if design is missing for some reason
        if (!empty($formDesignId) && $formDesignRegistrar->hasDesign($formDesignId)) {
            $design = $formDesignRegistrar->getDesign($formDesignId);

            if ($design->css()) {
                wp_enqueue_style('givewp-form-design-' . $design::id(), $design->css());
            }

            if ($design->js()) {
                wp_enqueue_script(
                    'givewp-form-design-' . $design::id(),
                    $design->js(),
                    array_merge(
                        ['givewp-donation-form-registrars'],
                        $design->dependencies()
                    ),
                    false,
                    true
                );
            }
        }

        // load receipt app
        wp_enqueue_script(
            'givewp-donation-confirmation-receipt',
            GIVE_PLUGIN_URL . 'build/donationConfirmationReceiptApp.js',
            array_merge(
                $this->getScriptAssetDependencies(GIVE_PLUGIN_DIR . 'build/donationConfirmationReceiptApp.asset.php'),
                ['givewp-donation-form-registrars']
            ),
            GIVE_VERSION,
            true
        );

        /**
         * Load iframeResizer.contentWindow.min.js inside iframe
         *
         * @see https://github.com/davidjbradshaw/iframe-resizer
         */
        wp_enqueue_script(
            'givewp-donation-form-embed-inside',
            GIVE_PLUGIN_URL . 'build/donationFormEmbedInside.js',
            [],
            GIVE_VERSION,
            true
        );
    }
}
