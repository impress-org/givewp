<?php

namespace Give\DonationForms\ViewModels;

use Give\DonationForms\Actions\GenerateAuthUrl;
use Give\DonationForms\Actions\GenerateDonateRouteUrl;
use Give\DonationForms\Actions\GenerateDonationFormValidationRouteUrl;
use Give\DonationForms\DataTransferObjects\DonationFormGoalData;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\DesignSystem\Actions\RegisterDesignSystemStyles;
use Give\Framework\FormDesigns\FormDesign;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\Helpers\Hooks;

/**
 * @since 0.1.0
 */
class DonationFormViewModel
{
    use HasScriptAssetFile;
    /**
     * @var int
     */
    private $donationFormId;
    /**
     * @var BlockCollection
     */
    private $formBlocks;
    /**
     *
     * @var FormSettings
     */
    private $formSettings;
    /**
     * @var DonationFormRepository
     */
    private $donationFormRepository;

    /**
     * @since 0.1.0
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
     * @since 0.1.0
     */
    public function designId(): string
    {
        return $this->formSettings->designId ?? '';
    }

    /**
     * @since 0.1.0
     */
    public function primaryColor(): string
    {
        return $this->formSettings->primaryColor ?? '';
    }

    /**
     * @since 0.1.0
     */
    public function secondaryColor(): string
    {
        return $this->formSettings->secondaryColor ?? '';
    }

    /**
     * @since 0.1.0
     */
    public function enqueueGlobalStyles()
    {
        (new RegisterDesignSystemStyles())();
        wp_enqueue_style('givewp-design-system-foundation');

        wp_register_style(
            'givewp-global-form-styles',
            GIVE_NEXT_GEN_URL . 'src/DonationForm/resources/styles/global.css'
        );

        wp_add_inline_style(
            'givewp-global-form-styles',
            ":root {
            --givewp-primary-color:{$this->primaryColor()};
            --givewp-secondary-color:{$this->secondaryColor()};
            }"
        );

        wp_enqueue_style('givewp-global-form-styles');

        wp_enqueue_style(
            'givewp-base-form-styles',
            GIVE_NEXT_GEN_URL . 'build/baseFormDesignCss.css'
        );
    }

    /**
     * @since 0.1.0
     */
    private function goalType(): GoalType
    {
        return $this->formSettings->goalType ?? GoalType::AMOUNT();
    }

    /**
     * @since 0.1.0
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
     * @since 0.6.0 update form object data to use DonationForm Node
     * @since 0.1.0
     */
    public function exports(): array
    {
        $donateUrl = (new GenerateDonateRouteUrl())();
        $validateUrl = (new GenerateDonationFormValidationRouteUrl())();
        $authUrl = (new GenerateAuthUrl())();
        $donationFormGoalData = new DonationFormGoalData($this->donationFormId, $this->formSettings);

        $formDataGateways = $this->donationFormRepository->getFormDataGateways($this->donationFormId);
        $formApi = $this->donationFormRepository->getFormSchemaFromBlocks(
            $this->donationFormId,
            $this->formBlocks
        );

        $formDesign = $this->getFormDesign($this->designId());

        return [
            'donateUrl' => $donateUrl,
            'validateUrl' => $validateUrl,
            'authUrl' => $authUrl,
            'inlineRedirectRoutes' => [
                'donation-confirmation-receipt-view'
            ],
            'registeredGateways' => $formDataGateways,
            'form' => array_merge($formApi->jsonSerialize(), [
                'settings' => $this->formSettings,
                'currency' => $formApi->getDefaultCurrency(),
                'goal' => $donationFormGoalData->toArray(),
                'stats' => $this->formStatsData(),
                'design' => $formDesign ? [
                    'id' => $formDesign::id(),
                    'name' => $formDesign::name(),
                    'isMultiStep' => $formDesign->isMultiStep(),
                ] : null,
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
     * @since 0.1.0
     */
    public function render(bool $preview = false): string
    {
        $this->enqueueGlobalStyles();

        $this->enqueueFormScripts(
            $this->donationFormId,
            $this->designId()
        );

        ob_start();
        wp_print_styles();
        wp_print_head_scripts();
        ?>

        <?php
        if ($this->formSettings->customCss): ?>
            <style><?php
                echo $this->formSettings->customCss; ?></style>
        <?php
        endif; ?>

        <?php
        $classNames = ['givewp-donation-form'];

        if ($preview) {
            $classNames[] = 'givewp-donation-form--preview';
        }
        ?>

        <div data-theme="light" id="root-givewp-donation-form"
             data-iframe-height
             class="<?= implode(' ', $classNames) ?>"></div>

        <?php
        wp_print_footer_scripts();

        echo ob_get_clean();

        exit();
    }

    /**
     * Loads scripts in order: [Registrars, Designs, Gateways, Block]
     *
     * @since 0.4.0 Add support for custom form extensions
     * @since 0.1.0
     *
     * @return void
     */
    private function enqueueFormScripts(int $formId, string $formDesignId)
    {
        $this->enqueueRegistrars();
        $this->enqueueDesign($formDesignId);
        $this->enqueueGateways($formId);
        $this->enqueueFormApp();
    }

    /**
     * @since 0.4.0
     *
     * @return FormDesign|null
     */
    protected function getFormDesign(string $designId)
    {
        /** @var FormDesignRegistrar $formDesignRegistrar */
        $formDesignRegistrar = give(FormDesignRegistrar::class);

        return $formDesignRegistrar->hasDesign($this->designId()) ? $formDesignRegistrar->getDesign($designId) : null;
    }

    /**
     * @since 0.5.0
     */
    private function enqueueRegistrars()
    {
        wp_enqueue_style(
            'givewp-donation-form-registrars',
            GIVE_NEXT_GEN_URL . 'build/donationFormRegistrars.css',
            [],
            GIVE_NEXT_GEN_VERSION
        );

        wp_enqueue_script(
            'givewp-donation-form-registrars',
            GIVE_NEXT_GEN_URL . 'build/donationFormRegistrars.js',
            $this->getScriptAssetDependencies(GIVE_NEXT_GEN_DIR . 'build/donationFormRegistrars.asset.php'),
            GIVE_NEXT_GEN_VERSION,
            true
        );

        wp_add_inline_script(
            'givewp-donation-form-registrars',
            'window.givewpDonationFormExports = ' . wp_json_encode($this->exports()) . ';',
            'before'
        );

        Hooks::doAction('givewp_donation_form_enqueue_scripts');
    }

    /**
     * @since 0.5.0
     */
    private function enqueueGateways(int $formId)
    {
        /** @var DonationFormRepository $donationFormRepository */
        $donationFormRepository = give(DonationFormRepository::class);

        // load gateway scripts
        foreach ($donationFormRepository->getEnabledPaymentGateways($formId) as $gateway) {
            if (method_exists($gateway, 'enqueueScript')) {
                $gateway->enqueueScript($formId);
            }
        }
    }

    /**
     * @since 0.5.0
     */
    private function enqueueDesign(string $formDesignId)
    {
        $design = $this->getFormDesign($formDesignId);

        // silently fail if design is missing for some reason
        if ($design) {
            if ($design->css()) {
                wp_enqueue_style('givewp-form-design-' . $design::id(), $design->css());
            }

            if ($design->js()) {
                wp_enqueue_script(
                    'givewp-form-design-' . $design::id(),
                    $design->js(),
                    array_merge(
                        $design->dependencies(),
                        ['givewp-donation-form-registrars']
                    ),
                    true
                );
            }
        }
    }

    /**
     * @since 0.5.0
     */
    private function enqueueFormApp()
    {
        // load block - since this is using render_callback viewScript in blocks.json will not work.
        wp_enqueue_script(
            'givewp-donation-form-app',
            GIVE_NEXT_GEN_URL . 'build/donationFormApp.js',
            array_merge(
                $this->getScriptAssetDependencies(GIVE_NEXT_GEN_DIR . 'build/donationFormApp.asset.php'),
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
