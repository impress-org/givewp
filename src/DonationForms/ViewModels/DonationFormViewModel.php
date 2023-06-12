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
use Give\Framework\EnqueueScript;
use Give\Framework\FormDesigns\FormDesign;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Helpers\Hooks;

use function implode;
use function wp_enqueue_style;
use function wp_print_styles;

/**
 * @since 0.1.0
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
        )->jsonSerialize();

        $formDesign = $this->getFormDesign($this->designId());

        return [
            'donateUrl' => $donateUrl,
            'validateUrl' => $validateUrl,
            'authUrl' => $authUrl,
            'inlineRedirectRoutes' => [
                'donation-confirmation-receipt-view'
            ],
            'registeredGateways' => $formDataGateways,
            'form' => array_merge($formApi, [
                'settings' => $this->formSettings,
                'currency' => give_get_currency(),
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

        <script>
            window.givewpDonationFormExports = <?= wp_json_encode($this->exports()) ?>;
        </script>

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
     * @unreleased Add support for custom form extensions
     * @since 0.1.0
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

        Hooks::doAction('givewp_donation_form_enqueue_scripts');

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
            'givewp-next-gen-donation-form-js',
            'build/donationFormApp.js',
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

    /**
     * @unreleased
     *
     * @return FormDesign|null
     */
    protected function getFormDesign(string $designId)
    {
        /** @var FormDesignRegistrar $formDesignRegistrar */
        $formDesignRegistrar = give(FormDesignRegistrar::class);

        return $formDesignRegistrar->hasDesign($this->designId()) ? $formDesignRegistrar->getDesign($designId) : null;
    }
}
