<?php

namespace Give\DonationForms\Shortcodes;

use Give\DonationForms\Actions\GenerateDonationFormViewRouteUrl;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;

class GiveFormShortcode
{
    /**
     * @var int $instance
     */
    public static $instance = 0;

    /**
     * @since 3.2.0 include v3 block attributes for shortcode.
     * @since 3.1.1 use static instance ID to simulate blockId attribute
     * @since 3.0.0
     */
    public function __invoke(string $output, array $atts): string
    {
        self::$instance++;

        $formId = absint($atts['id']);
        $isV3Form = (bool)give()->form_meta->get_meta($formId, 'formBuilderSettings', true);

        if (!$formId || !$isV3Form) {
            return $output;
        }

        $controller = new BlockRenderController();
        $blockAttributes = [
            'formId' => $formId,
            'blockId' => 'give-form-shortcode-' . self::$instance,
            'formFormat' => $atts['display_style'] ?? null,
            'openFormButton' => $atts['continue_button_title'] ?? null
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
