<?php

namespace Give\NextGen\DonationForm\Controllers;

use Give\Framework\EnqueueScript;
use Give\NextGen\DonationForm\DataTransferObjects\DonationFormViewRouteData;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\DonationForm\ViewModels\DonationFormViewModel;
use Give\NextGen\Framework\FormTemplates\Registrars\FormTemplateRegistrar;

class DonationFormViewController
{
    /**
     * This renders the donation form view.
     *
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
     *
     * @unreleased
     */
    public function show(DonationFormViewRouteData $data): string
    {
        $viewModel = new DonationFormViewModel($data->formId, $data->formBlocks);

        wp_enqueue_global_styles();
        $this->enqueueFormScripts($data->formId, $data->formTemplateId);

        ob_start();
        wp_print_styles();
        wp_print_head_scripts();
        ?>

        <script>
            window.giveNextGenExports = <?= wp_json_encode($viewModel->exports()) ?>;
        </script>

        <div id="root-give-next-gen-donation-form-block"></div>

        <?php
        wp_print_footer_scripts();
        echo ob_get_clean();

        exit();
    }

     /**
     * Loads scripts in order: [Registrars, Template, Gateways, Block]
     *
     * @unreleased
     *
     * @return void
     */
    private function enqueueFormScripts(int $formId, string $formTemplateId)
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
        /** @var FormTemplateRegistrar $formTemplateRegistrar */
        $formTemplateRegistrar = give(FormTemplateRegistrar::class);

        // silently fail if template is missing for some reason
        if ($formTemplateRegistrar->hasTemplate($formTemplateId)) {
            $template = $formTemplateRegistrar->getTemplate($formTemplateId);

            if ($template->css()) {
                wp_enqueue_style('givewp-form-template-' . $template->getId(), $template->css());
            }

            if ($template->js()) {
                wp_enqueue_script(
                    'givewp-form-template-' . $template->getId(),
                    $template->js(),
                    array_merge(
                        ['givewp-donation-form-registrars-js'],
                        $template->dependencies()
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
