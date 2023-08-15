<?php

namespace Give\DonationForms\Shortcodes;

use Give\DonationForms\Actions\GenerateDonationFormViewRouteUrl;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;

class GiveFormShortcode
{
    /**
     * @since 0.5.0
     */
    public function __invoke(string $output, array $atts): string
    {
        $formId = absint($atts['id']);
        $isV3Form = (bool) give()->form_meta->get_meta($formId, 'formBuilderSettings', true);

        if (!$formId || !$isV3Form) {
            return $output;
        }

        $controller = new BlockRenderController();
        $blockAttributes = [
            'formId' => $formId,
            'blockId' => 'give-form-shortcode-' . uniqid(),
        ];

        $output = $controller->render($blockAttributes);

        if (!$output) {
            $viewUrl = (new GenerateDonationFormViewRouteUrl())($formId);
            $output = sprintf(
                "<iframe
                    src='%s'
                    style='width: 1px;min-width: 100%%;border: 0;transition: height 0.25s;'
                    onload='if( \"undefined\" !== typeof Give ) { Give.initializeIframeResize(this) }'
                ></iframe>",
                esc_url($viewUrl)
            );
        }

        return $output;
    }
}
