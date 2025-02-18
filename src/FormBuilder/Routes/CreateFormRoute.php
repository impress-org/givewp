<?php

namespace Give\FormBuilder\Routes;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;
use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\Helpers\Hooks;
use Give\Helpers\Language;

/**
 * Route to create a new form
 */
class CreateFormRoute
{
    /**
     * @unreleased Add locale support
     * @since 3.1.0 updated default form blocks to be generated from block models instead of json
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     */
    public function __invoke()
    {
        if (isset($_GET['page']) && FormBuilderRouteBuilder::SLUG === $_GET['page']) {
            $locale = $_GET['locale'] ?? '';

            // Little hack for alpha users to make sure the form builder is loaded.
            if (!isset($_GET['donationFormID'])) {
                wp_redirect(FormBuilderRouteBuilder::makeCreateFormRoute($locale)->getUrl());
                exit();
            }
            if ('new' === $_GET['donationFormID']) {
                // Make sure the Form will be created using the proper locale
                $locale = $_GET['locale'] ?? '';
                Language::switchToLocale($locale);

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

                wp_redirect(FormBuilderRouteBuilder::makeEditFormRoute($form->id, $locale)->getUrl());
                exit();
            }
        }
    }
}
