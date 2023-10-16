<?php

namespace Give\FormBuilder\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Blocks\BlockModel;

/**
 * In v2 forms, there was a concept of "Default Options" in global GiveWP settings.
 * In v3 forms, we have "Default Blocks" instead.  This action converts the global default options into default blocks.
 *
 * @since 3.0.0
 */
class ConvertGlobalDefaultOptionsToDefaultBlocks
{
    /**
     * @since 3.0.0
     */
    public function __invoke(DonationForm $form)
    {
        $this->handleDonorComments($form);
        $this->handleAnonymousDonations($form);
    }

    /**
     * @since 3.0.0
     */
    protected function handleDonorComments(DonationForm $form)
    {
        if (give_is_donor_comment_field_enabled($form->id) && !$form->blocks->findByName('givewp/donor-comments')) {
            $block = BlockModel::make([
                'name' => 'givewp/donor-comments',
                'attributes' => [
                    'label' => __('Comment', 'give'),
                    'description' => __('Would you like to add a comment to this donation?', 'give'),
                ],
            ]);

            $form->blocks->insertAfter('givewp/email', $block);
        }
    }

    /**
     * @since 3.0.0
     */
    protected function handleAnonymousDonations(DonationForm $form)
    {
        if (give_is_anonymous_donation_field_enabled($form->id) && !$form->blocks->findByName('givewp/anonymous')) {
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