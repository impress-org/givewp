<?php

namespace Give\Campaigns\Actions;

use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 */
class RenderDonateButton
{
    /**
     * @unreleased
     */
    public function __invoke(int $formId, string $buttonText): string
    {
        if (!$this->isFormPublished($formId)) {
            return '';
        }

        $blockRender = (new BlockRenderController())->render([
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
     * @unreleased
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
