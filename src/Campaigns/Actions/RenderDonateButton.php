<?php

namespace Give\Campaigns\Actions;

use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\DonationForms\Models\DonationForm;

/**
 * @since 4.0.0
 */
class RenderDonateButton
{
    /**
     * @since 4.0.0
     */
    private BlockRenderController $blockRenderController;

    /**
     * @since 4.0.0
     */
    public function __construct(BlockRenderController $blockRenderController)
    {
        $this->blockRenderController = $blockRenderController;
    }

    /**
     * @since 4.0.0
     */
    public function __invoke(int $formId, string $buttonText): string
    {
        if (!$this->isFormPublished($formId)) {
            return '';
        }

        $blockRender = $this->blockRenderController->render([
            'formId' => $formId,
            'openFormButton' => esc_html($buttonText),
            'formFormat' => 'modal',
        ]);

        return $blockRender ?? sprintf(
            '<button type="button" class="givewp-donation-form-modal__open">%s</button>',
            esc_html($buttonText)
        );
    }

    /**
     * @since 4.0.0
     */
    private function isFormPublished(int $formId): bool
    {
        if (!$formId) {
            return false;
        }

        /** @var DonationForm $form */
        $form = DonationForm::find($formId);

        return $form && $form->status->isPublished();
    }
}
