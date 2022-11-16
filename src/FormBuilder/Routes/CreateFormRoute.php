<?php

namespace Give\FormBuilder\Routes;

use Exception;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\ValueObjects\DonationFormStatus;
use Give\NextGen\DonationForm\ValueObjects\GoalTypeOptions;
use Give\NextGen\Framework\Blocks\BlockCollection;

/**
 * Route to create a new form
 */
class CreateFormRoute
{
    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function __invoke()
    {
        if (isset($_GET['page']) && 'campaign-builder' === $_GET['page']) {
            // Little hack for alpha users to make sure the form builder is loaded.
            if (!isset($_GET['donationFormID'])) {
                wp_redirect('edit.php?post_type=give_forms&page=campaign-builder&donationFormID=new');
                exit();
            }
            if ('new' === $_GET['donationFormID']) {
                $blocksJson = file_get_contents(GIVE_NEXT_GEN_DIR . 'packages/form-builder/src/blocks.json');

                $form = DonationForm::create([
                    'title' => __('GiveWP Donation Form', 'give'),
                    'status' => DonationFormStatus::PUBLISHED(),
                    'settings' => [
                        'enableDonationGoal' => false,
                        'enableAutoClose' => false,
                        'registration' => 'none',
                        'goalType' => GoalTypeOptions::AMOUNT,
                    ],
                    'blocks' => BlockCollection::fromJson($blocksJson)
                ]);

                wp_redirect('edit.php?post_type=give_forms&page=campaign-builder&donationFormID=' . $form->id);
                exit();
            }
        }
    }
}
