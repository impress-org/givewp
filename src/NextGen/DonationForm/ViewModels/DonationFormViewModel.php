<?php

namespace Give\NextGen\DonationForm\ViewModels;

use Give\Framework\EnqueueScript;
use Give\NextGen\DonationForm\Actions\GenerateDonateRouteUrl;
use Give\NextGen\DonationForm\DataTransferObjects\DonationFormGoalData;
use Give\NextGen\DonationForm\Properties\FormSettings;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\DonationForm\ValueObjects\GoalType;
use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\FormDesigns\Registrars\FormDesignRegistrar;

/**
 * @unreleased
 */
class DonationFormViewModel
{
    /**
     * @var int
     */
    private $donationFormId;
    /**
     * @var BlockCollection
     */
    private $formBlocks;
    /**
     * TODO: replace formSettings array with $donationForm->settings object when property gets updated
     *
     * @var array{designId: string, primaryColor: string, secondaryColor: string, goalType: string}
     */
    private $formSettings;
    /**
     * @var DonationFormRepository
     */
    private $donationFormRepository;

    /**
     * @unreleased
     */
    public function __construct(
        int $donationFormId,
        BlockCollection $formBlocks,
        FormSettings $formSettings
    ) {
        $this->donationFormId = $donationFormId;
        $this->formBlocks = $formBlocks;
        $this->formSettings = $formSettings;
        $this->donationFormRepository = give(DonationFormRepository::class);
    }

    /**
     * @unreleased
     */
    public function designId(): string
    {
        return $this->formSettings->designId ?? '';
    }

    /**
     * @unreleased
     */
    public function primaryColor(): string
    {
        return $this->formSettings->primaryColor ?? '';
    }

    /**
     * @unreleased
     */
    public function secondaryColor(): string
    {
        return $this->formSettings->secondaryColor ?? '';
    }

    /**
     * @unreleased
     */
    private function goalType(): GoalType
    {
        return $this->formSettings->goalType ?? GoalType::AMOUNT();
    }

    /**
     * @unreleased
     */
    private function formStatsData(): array
    {
        $totalRevenue = $this->donationFormRepository->getTotalRevenue($this->donationFormId);
        $goalType = $this->goalType();

        return [
            'totalRevenue' => $totalRevenue,
            'totalCountValue' => $goalType->isDonors() ?
                $this->donationFormRepository->getTotalNumberOfDonors($this->donationFormId) :
                $this->donationFormRepository->getTotalNumberOfDonations($this->donationFormId),
            'totalCountLabel' => $goalType->isDonors() ? __('donors', 'give') : __(
                'donations',
                'give'
            ),
        ];
    }

    /**
     * @unreleased
     */
    public function exports(): array
    {
        $donateUrl = (new GenerateDonateRouteUrl())();
        $donationFormGoalData = new DonationFormGoalData($this->donationFormId, $this->formSettings);

        $formDataGateways = $this->donationFormRepository->getFormDataGateways($this->donationFormId);
        $formApi = $this->donationFormRepository->getFormSchemaFromBlocks(
            $this->donationFormId,
            $this->formBlocks
        )->jsonSerialize();

        return [
            'donateUrl' => $donateUrl,
            'successUrl' => give_get_success_page_uri(),
            'gatewaySettings' => $formDataGateways,
            'form' => array_merge($formApi, [
                'settings' => $this->formSettings,
                'currency' => give_get_currency(),
                'goal' => $donationFormGoalData->toArray(),
                'stats' => $this->formStatsData()
            ]),
        ];
    }

    /**
     * This is the order of loading:
     * 1. Enqueue global styles from WP.
     *  - This ensures template compatability with global WP css variables as needed. Loads before our templates, so they can use things like global font-family, etc.
     * 2. Enqueue our donation form specific scripts & styles.
     *  - We will let WP handle the actual printing depending on how they were enqueued.
     * 3. Call the specific WP functions wp_print_styles() and wp_print_head_scripts()
     *  - This will only print the styles and scripts that are enqueued within our route - so we don't have to dequeue a bunch of stuff.
     * 4. Manually echo our window data and root div for our React app to consume
     * 5. Finally, call the specific WP function wp_print_footer_scripts()
     *  - This will only print the footer scripts that are enqueued within our route.
     *
     * @unreleased
     */
    public function render(): string
    {
        wp_enqueue_global_styles();

        $this->enqueueFormScripts(
            $this->donationFormId,
            $this->designId()
        );

        ob_start();
        wp_print_styles();
        wp_print_head_scripts();
        ?>

        <script>
            window.giveNextGenExports = <?= wp_json_encode($this->exports()) ?>;
        </script>

        <div
            id="root-give-next-gen-donation-form-block"
            class="givewp-donation-form-block"
            style="
                --give-primary-color:<?= $this->primaryColor() ?>;
                --give-secondary-color:<?= $this->secondaryColor() ?>;
                "
        ></div>

        <?php
        wp_print_footer_scripts();

        echo ob_get_clean();

        exit();
    }

    /**
     * Loads scripts in order: [Registrars, Designs, Gateways, Block]
     *
     * @unreleased
     *
     * @return void
     */
    private function enqueueFormScripts(int $formId, string $formDesignId)
    {
        /** @var DonationFormRepository $donationFormRepository */
        $donationFormRepository = give(DonationFormRepository::class);

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

        // load gateways
        foreach ($donationFormRepository->getEnabledPaymentGateways($formId) as $gateway) {
            if (method_exists($gateway, 'enqueueScript')) {
                /** @var EnqueueScript $script */
                $script = $gateway->enqueueScript();

                $script->dependencies(['givewp-donation-form-registrars-js'])
                    ->loadInFooter()
                    ->enqueue();
            }
        }

        // load block - since this is using render_callback viewScript in blocks.json will not work.
        (new EnqueueScript(
            'givewp-next-gen-donation-form-block-js',
            'build/donationFormBlockApp.js',
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
