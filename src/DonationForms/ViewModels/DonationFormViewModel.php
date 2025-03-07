<?php

namespace Give\DonationForms\ViewModels;

use Give\DonationForms\Actions\GenerateAuthUrl;
use Give\DonationForms\Actions\GenerateDonateRouteUrl;
use Give\DonationForms\Actions\GenerateDonationFormValidationRouteUrl;
use Give\DonationForms\DataTransferObjects\DonationFormGoalData;
use Give\DonationForms\DonationQuery;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\DesignSystem\Actions\RegisterDesignSystemStyles;
use Give\Framework\FormDesigns\FormDesign;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\Helpers\Hooks;
use Give\Helpers\Language;

/**
 * @since 3.0.0
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
     * @var bool
     */
    private $previewMode;

    /**
     * @since 3.0.0
     */
    public function __construct(
        int $donationFormId,
        BlockCollection $formBlocks,
        FormSettings $formSettings,
        bool $previewMode = false
    ) {
        $this->donationFormId = $donationFormId;
        $this->formBlocks = $formBlocks;
        $this->formSettings = $formSettings;
        $this->donationFormRepository = give(DonationFormRepository::class);
        $this->previewMode = $previewMode;
    }

    /**
     * @since 3.0.0
     */
    public function designId(): string
    {
        return $this->formSettings->designId ?? '';
    }

    /**
     * @since 3.0.0
     */
    public function primaryColor(): string
    {
        return $this->formSettings->primaryColor ?? '';
    }

    /**
     * @since 3.0.0
     */
    public function secondaryColor(): string
    {
        return $this->formSettings->secondaryColor ?? '';
    }

    /**
     * @since 3.0.0
     */
    public function enqueueGlobalStyles()
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
            --givewp-primary-color:{$this->primaryColor()};
            --givewp-secondary-color:{$this->secondaryColor()};
            }"
        );

        wp_enqueue_style('givewp-base-form-styles');
    }

    /**
     * @since 3.0.0
     */
    private function goalType(): GoalType
    {
        return $this->formSettings->goalType ?? GoalType::AMOUNT();
    }

    /**
     * @since 3.0.0
     */
    private function getTotalCountValue(GoalType $goalType): ?int
    {
        if ($goalType->isDonors()) {
            return $this->donationFormRepository->getTotalNumberOfDonors($this->donationFormId);
        }

        if ($goalType->isDonations() || $goalType->isAmount()) {
            return $this->donationFormRepository->getTotalNumberOfDonations($this->donationFormId);
        }

        if ($goalType->isSubscriptions() || $goalType->isAmountFromSubscriptions()) {
            return $this->donationFormRepository->getTotalNumberOfSubscriptions($this->donationFormId);
        }

        if ($goalType->isDonorsFromSubscriptions()) {
            return $this->donationFormRepository->getTotalNumberOfDonorsFromSubscriptions($this->donationFormId);
        }

        return 0;
    }

    /**
     * @since 3.0.0
     */
    private function getCountLabel(GoalType $goalType): ?string
    {
        if ($goalType->isDonors() || $goalType->isDonorsFromSubscriptions()) {
            return __('Donors', 'give');
        }

        if ($goalType->isDonations() || $goalType->isAmount()) {
            return __('Donations', 'give');
        }

        if ($goalType->isSubscriptions() || $goalType->isAmountFromSubscriptions()) {
            return __('Recurring Donations', 'give');
        }

        return __('Counted', 'give');
    }

    /**
     * @since 3.0.0
     */
    private function getTotalRevenue(GoalType $goalType)
    {
        if ($goalType->isAmountFromSubscriptions()) {
            return $this->donationFormRepository->getTotalInitialAmountFromSubscriptions($this->donationFormId);
        }

        return $this->donationFormRepository->getTotalRevenue($this->donationFormId);
    }

    /**
     * @since 3.0.0
     */
    private function formStatsData(): array
    {
        $goalType = $this->goalType();

        $donationQuery = (new DonationQuery)->form($this->donationFormId);

        if($this->formSettings->goalProgressType->isCustom()) {
            $donationQuery->between($this->formSettings->goalStartDate, $this->formSettings->goalEndDate);
        }

        return [
            'totalRevenue' => $donationQuery->sumIntendedAmount(),
            'totalCountValue' => $goalType->isDonations() || $goalType->isAmount()
                ? $donationQuery->count()
                : $this->getTotalCountValue($goalType),
            'totalCountLabel' => $this->getCountLabel($goalType),
        ];
    }

    /**
     * @since 3.6.0 added includeHeaderInMultiStep to form design export
     * @since 3.0.0
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
                'donation-confirmation-receipt-view',
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
                    'includeHeaderInMultiStep' => $formDesign->shouldIncludeHeaderInMultiStep(),
                ] : null,
            ]),
            'previewMode' => $this->previewMode,
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
     * @since 3.20.0 Adds class for form design
     * @since 3.11.0 Sanitize customCSS property
     * @since 3.0.0
     */
    public function render(): string
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
        if ($this->previewMode || $this->formSettings->customCss): ?>
            <style id="root-givewp-donation-form-style"><?php
                echo wp_strip_all_tags($this->formSettings->customCss); ?></style>
        <?php
        endif; ?>

        <?php
        $classNames = ['givewp-donation-form', "givewp-donation-form-design--{$this->designId()}"];

        if ($this->previewMode) {
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
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
     */
    private function enqueueRegistrars()
    {
        wp_enqueue_style(
            'givewp-donation-form-registrars',
            GIVE_PLUGIN_URL . 'build/donationFormRegistrars.css',
            [],
            GIVE_VERSION
        );

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
            'window.givewpDonationFormExports = ' . wp_json_encode($this->exports()) . ';',
            'before'
        );

        Hooks::doAction('givewp_donation_form_enqueue_scripts');
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0 Set script translations
     * @since 3.0.0
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
                $handle = 'givewp-form-design-' . $design::id();
                wp_enqueue_script(
                    $handle,
                    $design->js(),
                    array_merge(
                        $design->dependencies(),
                        ['givewp-donation-form-registrars']
                    ),
                    true
                );

                Language::setScriptTranslations($handle);
            }
        }
    }

    /**
     * @since 3.0.0
     */
    private function enqueueFormApp()
    {
        // load block - since this is using render_callback viewScript in blocks.json will not work.
        $handle = 'givewp-donation-form-app';
        wp_enqueue_script(
            $handle,
            GIVE_PLUGIN_URL . 'build/donationFormApp.js',
            array_merge(
                $this->getScriptAssetDependencies(GIVE_PLUGIN_DIR . 'build/donationFormApp.asset.php'),
                ['givewp-donation-form-registrars']
            ),
            GIVE_VERSION,
            true
        );

        Language::setScriptTranslations($handle);

        /**
         * Load iframeResizer.contentWindow.min.js inside iframe
         *
         * @see https://github.com/davidjbradshaw/iframe-resizer
         */
        $handle = 'givewp-donation-form-embed-inside';
        wp_enqueue_script(
            $handle,
            GIVE_PLUGIN_URL . 'build/donationFormEmbedInside.js',
            [],
            GIVE_VERSION,
            true
        );

        Language::setScriptTranslations($handle);
    }

    /**
     * @since 3.4.0
     */
    private function updateDesignSettingsClassNames(array &$classNames)
    {
        if ($this->formSettings->designSettingsImageUrl) {
            $classNames[] = 'givewp-design-settings--image';
            $classNames[] = 'givewp-design-settings--image-style__' . $this->formSettings->designSettingsImageStyle;
        }

        if ($this->formSettings->designSettingsLogoUrl) {
            $classNames[] = 'givewp-design-settings--logo';
            $classNames[] = 'givewp-design-settings--logo-position__' . $this->formSettings->designSettingsLogoPosition;
        }

        $classNames[] = 'givewp-design-settings--section-style__' . $this->formSettings->designSettingsSectionStyle;

        $classNames[] = 'givewp-design-settings--textField-style__' . $this->formSettings->designSettingsTextFieldStyle;
    }
}
