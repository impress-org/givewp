<?php

namespace Give\FormBuilder\Actions;

use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 */
class UpdatePdfSettingsMeta
{
    /**
     * @unreleased
     *
     * @param DonationForm $form
     */
    public function __invoke(DonationForm $form)
    {
        $pdfSettings = $form->settings->pdfSettings;

        give()->form_meta->update_meta($form->id, "give_pdf_receipts_enable_disable",
            $pdfSettings->enable ? 'enabled' : 'global');

        give()->form_meta->update_meta($form->id, "give_pdf_generation_method", $pdfSettings->generationMethod);
    }
}
