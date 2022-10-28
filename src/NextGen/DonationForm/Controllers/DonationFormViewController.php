<?php

namespace Give\NextGen\DonationForm\Controllers;

use Give\Framework\EnqueueScript;
use Give\NextGen\DonationForm\DataTransferObjects\DonationFormViewRouteData;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\DonationForm\ViewModels\DonationFormViewModel;
use Give\NextGen\Framework\FormTemplates\Registrars\FormTemplateRegistrar;

class DonationFormViewController {
    /**
     * @unreleased
     */
    public function show(DonationFormViewRouteData $data): string
    {
        $viewModel = new DonationFormViewModel($data->formId, $data->formBlocks);

        ob_start();
        $this->head();
        $this->enqueueScripts($data->formId, $data->formTemplateId);
        ?>

        <script>window.giveNextGenExports = <?= wp_json_encode($viewModel->exports()) ?>;</script>

        <div id="root-give-next-gen-donation-form-block"></div>

        <?php
        wp_print_footer_scripts();
        echo ob_get_clean();

        exit();
    }

    /**
     * @unreleased
     *
     * @return void
     */
    private function head()
    {
        global $wp_scripts, $wp_styles;
        wp_head();

        $wp_styles->dequeue(array_column($wp_styles->registered,'handle'));
        $wp_scripts->dequeue(array_column($wp_scripts->registered,'handle'));
    }

     /**
     * Loads scripts in order: [Registrars, Template, Gateways, Block]
     *
     * @unreleased
     *
     * @return void
     */
    private function enqueueScripts(int $formId, string $formTemplateId)
    {
        /** @var DonationFormRepository $donationFormRepository */
        $donationFormRepository = give(DonationFormRepository::class);

        // load registrars
        (new EnqueueScript(
            'give-donation-form-registrars-js',
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
                        ['give-donation-form-registrars-js'],
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

                $script->dependencies(['give-donation-form-registrars-js'])
                    ->loadInFooter()
                    ->enqueue();
            }
        }

        // load block - since this is using render_callback viewScript in blocks.json will not work.
        (new EnqueueScript(
            'give-next-gen-donation-form-block-js',
            'build/donationFormBlockApp.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ))->dependencies(['give-donation-form-registrars-js'])->loadInFooter()->enqueue();

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
