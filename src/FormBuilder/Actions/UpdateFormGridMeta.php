<?php

namespace Give\FormBuilder\Actions;

use Give\DonationForms\Models\DonationForm;

/**
 * Update email settings on backwards compatible form meta.
 *
 * @since 0.6.0
 */
class UpdateFormGridMeta
{
    /**
     * @since 0.6.0
     * @param  DonationForm  $form
     */
    public function __invoke(DonationForm $form)
    {
        give()->form_meta->update_meta($form->id, "_give_form_grid_option", $form->settings->formGridCustomize ? 'custom' : 'global');
        give()->form_meta->update_meta($form->id, "_give_form_grid_redirect_url", $form->settings->formGridRedirectUrl);
        give()->form_meta->update_meta($form->id, "_give_form_grid_donate_button_text", $form->settings->formGridDonateButtonText);
    }
}
