<?php

namespace Give\FormBuilder\Actions;

use Give\DonationForms\Models\DonationForm;

/**
 * Update donor comments meta on backwards compatible form meta.
 *
 * @since 0.7.0
 */
class UpdateDonorCommentsMeta
{
    /**
     * @since 0.7.0
     */
    public function __invoke(DonationForm $form)
    {
        if ($form->blocks->findByName('givewp/donor-comments') && !give_is_donor_comment_field_enabled($form->id)) {
            give()->form_meta->update_meta($form->id, '_give_donor_comment', 'enabled');
        }
    }
}
