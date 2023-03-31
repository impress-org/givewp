<?php

namespace Give\FormBuilder\Routes;

use Exception;
use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\Properties\FormSettings;
use Give\NextGen\DonationForm\ValueObjects\DonationFormStatus;
use Give\NextGen\Framework\Blocks\BlockCollection;

/**
 * Route to create a new form
 */
class CreateFormRoute
{
    /**
     * @since 0.1.0
     *
     * @return void
     * @throws Exception
     */
    public function __invoke()
    {
        if (isset($_GET['page']) && FormBuilderRouteBuilder::SLUG === $_GET['page']) {
            // Little hack for alpha users to make sure the form builder is loaded.
            if (!isset($_GET['donationFormID'])) {
                wp_redirect(FormBuilderRouteBuilder::makeCreateFormRoute());
                exit();
            }
            if ('new' === $_GET['donationFormID']) {
                $blocksJson = file_get_contents(GIVE_NEXT_GEN_DIR . 'packages/form-builder/src/blocks.json');

                $form = DonationForm::create([
                    'title' => __('GiveWP Donation Form', 'give'),
                    'status' => DonationFormStatus::DRAFT(),
                    'settings' => FormSettings::fromArray([
                        'goalAmount' => 1000,
                    ]),
                    'blocks' => BlockCollection::fromJson($blocksJson)
                ]);

                wp_redirect(FormBuilderRouteBuilder::makeEditFormRoute($form->id));
                exit();
            }
        }
    }
}
