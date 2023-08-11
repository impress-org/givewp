<?php

namespace Give\FormBuilder\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Blocks\BlockModel;

/**
 * In v2 forms, there was a concept of "Default Options" in global GiveWP settings.
 * In v3 forms, we have "Default Blocks" instead.  This action converts the global default options into default blocks.
 *
 * @unreleased
 */
class ConvertGlobalDefaultOptionsToDefaultBlocks
{
    /**
     * @unreleased
     */
    public function __invoke(DonationForm $form)
    {
        $this->handleAnonymousDonations($form);
    }

    /**
     * @unreleased
     */
    protected function handleAnonymousDonations(DonationForm $form)
    {
        if (give_is_anonymous_donation_field_enabled($form->id)) {
            $anonymousDonationsBlock = BlockModel::make([
                'name' => 'givewp/anonymous',
                'attributes' => [
                    'label' => __('Make this an anonymous donation.', 'give'),
                    'description' => __(
                        'Would you like to prevent your name, image, and comment from being displayed publicly?',
                        'give'
                    ),
                ],
            ]);

            $form->blocks->insertAfter('givewp/email', $anonymousDonationsBlock);
        }
    }
}