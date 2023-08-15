<?php

namespace Give\DonationForms\ViewModels;

use Give\DonationForms\FormDesigns\DeveloperFormDesign\DeveloperFormDesign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Donations\Models\Donation;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Receipts\DonationReceiptBuilder;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;

/**
 * @since 0.1.0
 */
class DonationConfirmationReceiptViewModel
{
    use HasScriptAssetFile;

    /**
     * @var Donation
     */
    public $donation;

    /**
     * @since 0.1.0
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    /**
     * @since 0.1.0
     */
    public function getDonationForm(): DonationForm
    {
        return DonationForm::find($this->donation->formId);
    }

    /**
     * @since 0.1.0
     */
    public function getReceipt(): DonationReceipt
    {
        $receipt = new DonationReceipt($this->donation);

        return (new DonationReceiptBuilder($receipt))->toConfirmationPage();
    }

    /**
     * @since 0.1.0
     */
    public function exports(): array
    {
        return [
            'receipt' => $this->getReceipt()->toArray()
        ];
    }

    /**
     * @since 0.1.0
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
     * @since 0.1.0
     */
    public function render(): string
    {
        /** @var DonationFormRepository $donationFormRepository */
        $donationFormRepository = give(DonationFormRepository::class);

        $donationForm = !$donationFormRepository->isLegacyForm(
            $this->donation->formId
        ) ? $this->getDonationForm() : null;
        
        $formDesignId = $donationForm ? $donationForm->settings->designId : DeveloperFormDesign::id();
        $customCss = $donationForm && $donationForm->settings->customCss ? $donationForm->settings->customCss : null;
        $primaryColor = $donationForm ? $donationForm->settings->primaryColor : '#69B868';
        $secondaryColor = $donationForm ? $donationForm->settings->secondaryColor : '#000000';

        $this->enqueueGlobalStyles($primaryColor, $secondaryColor);

        $this->enqueueFormScripts(
            $this->donation->formId,
            $formDesignId
        );

        ob_start();
        wp_print_styles();
        wp_print_head_scripts();
        ?>

        <?php
        if ($customCss): ?>
            <style><?= $customCss ?></style>
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
     * @since 0.1.0
     */
    public function enqueueGlobalStyles(string $primaryColor, string $secondaryColor)
    {
        wp_register_style(
            'givewp-global-form-styles',
            GIVE_NEXT_GEN_URL . 'src/DonationForm/resources/styles/global.css'
        );

        wp_add_inline_style(
            'givewp-global-form-styles',
            ":root {
            --givewp-primary-color:{$primaryColor};
            --givewp-secondary-color:{$secondaryColor}; 
            }"
        );

        wp_enqueue_style('givewp-global-form-styles');

        wp_enqueue_style(
            'givewp-base-form-styles',
            GIVE_NEXT_GEN_URL . 'build/baseFormDesignCss.css'
        );
    }

    /**
     * Loads scripts in order: [Registrars, Designs, App]
     *
     * @since 0.1.0
     *
     * @return void
     */
    private function enqueueFormScripts(int $formId, string $formDesignId)
    {
        wp_enqueue_script(
            'givewp-donation-form-registrars',
            GIVE_NEXT_GEN_URL . 'build/donationFormRegistrars.js',
            $this->getScriptAssetDependencies(GIVE_NEXT_GEN_DIR . 'build/donationFormRegistrars.asset.php'),
            GIVE_NEXT_GEN_VERSION,
            true
        );

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
        if ($formDesignRegistrar->hasDesign($formDesignId)) {
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
            GIVE_NEXT_GEN_URL . 'build/donationConfirmationReceiptApp.js',
            array_merge(
                $this->getScriptAssetDependencies(GIVE_NEXT_GEN_DIR . 'build/donationConfirmationReceiptApp.asset.php'),
                ['givewp-donation-form-registrars']
            ),
            GIVE_NEXT_GEN_VERSION,
            true
        );

        /**
         * Load iframeResizer.contentWindow.min.js inside iframe
         *
         * @see https://github.com/davidjbradshaw/iframe-resizer
         */
        wp_enqueue_script(
            'givewp-donation-form-embed-inside',
            GIVE_NEXT_GEN_URL . 'build/donationFormEmbedInside.js',
            [],
            GIVE_NEXT_GEN_VERSION,
            true
        );
    }
}
