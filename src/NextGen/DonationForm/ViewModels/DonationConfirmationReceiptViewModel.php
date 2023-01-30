<?php

namespace Give\NextGen\DonationForm\ViewModels;

use Give\Donations\Models\Donation;
use Give\Framework\EnqueueScript;
use Give\NextGen\DonationForm\FormDesigns\DeveloperFormDesign\DeveloperFormDesign;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\NextGen\Framework\Receipts\DonationReceipt;
use Give\NextGen\Framework\Receipts\DonationReceiptBuilder;

/**
 * @since 0.1.0
 */
class DonationConfirmationReceiptViewModel
{
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
            'gatewaySettings' => $formDataGateways,
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

        wp_enqueue_global_styles();

        $this->enqueueFormScripts(
            $this->donation->formId,
            $formDesignId
        );

        ob_start();
        wp_print_styles();
        wp_print_head_scripts();
        ?>

        <script>
            window.giveNextGenExports = <?= wp_json_encode($this->formExports()) ?>;
            window.givewpDonationConfirmationReceiptExports = <?= wp_json_encode($this->exports()) ?>;
        </script>

        <?php
        if ($customCss): ?>
            <style><?= $customCss ?></style>
        <?php
        endif; ?>

        <div id="root-givewp-donation-confirmation-receipt" class="givewp-donation-confirmation-receipt"
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
     * Loads scripts in order: [Registrars, Designs, App]
     *
     * @since 0.1.0
     *
     * @return void
     */
    private function enqueueFormScripts(int $formId, string $formDesignId)
    {
        // load registrars
        (new EnqueueScript(
            'givewp-donation-form-registrars-js',
            'build/donationFormRegistrars.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ))->loadInFooter()->enqueue();

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
                        ['givewp-donation-form-registrars-js'],
                        $design->dependencies()
                    ),
                    false,
                    true
                );
            }
        }

        // load receipt app
        (new EnqueueScript(
            'givewp-donation-confirmation-receipt-js',
            'build/donationConfirmationReceiptApp.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ))->dependencies(['givewp-donation-form-registrars-js'])->loadInFooter()->enqueue();

        /**
         * Load iframeResizer.contentWindow.min.js inside iframe
         *
         * @see https://github.com/davidjbradshaw/iframe-resizer
         */
        (new EnqueueScript(
            'givewp-donation-form-embed-inside',
            'build/donationFormEmbedInside.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ))->loadInFooter()->enqueue();
    }
}
