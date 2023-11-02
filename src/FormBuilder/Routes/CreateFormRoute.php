<?php

namespace Give\FormBuilder\Routes;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;
use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\Helpers\Hooks;

/**
 * Route to create a new form
 */
class CreateFormRoute
{
    /**
     * @since 3.1.0 updated default form blocks to be generated from block models instead of json
     * @since 3.0.0
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
                $form = new DonationForm([
                    'title' => __('GiveWP Donation Form', 'give'),
                    'status' => DonationFormStatus::DRAFT(),
                    'settings' => FormSettings::fromArray([
                        'enableDonationGoal' => true,
                        'goalAmount' => 1000,
                    ]),
                    'blocks' => (new GenerateDefaultDonationFormBlockCollection())(),
                ]);

                Hooks::doAction('givewp_form_builder_new_form', $form);

                $form->save();

                wp_redirect(FormBuilderRouteBuilder::makeEditFormRoute($form->id));
                exit();
            }
        }
    }
}
