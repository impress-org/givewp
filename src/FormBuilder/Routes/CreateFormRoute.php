<?php

namespace Give\FormBuilder\Routes;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;
use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\Framework\Permissions\Facades\UserPermissions;
use Give\Helpers\Hooks;
use Give\Helpers\Language;

/**
 * Route to create a new form
 */
class CreateFormRoute
{
    /**
     * @since TBD Use a safe redirect, check the capability and the nonce before creating the form, and sanitize the request input.
     * @since 4.14.2 update default form title
     * @since 3.22.0 Add locale support
     * @since 3.1.0 updated default form blocks to be generated from block models instead of json
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     */
    public function __invoke()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- these params only match the create route; the nonce is verified below before the form is created.
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        $donationFormId = isset($_GET['donationFormID']) ? sanitize_text_field(wp_unslash($_GET['donationFormID'])) : '';
        $locale = isset($_GET['locale']) ? sanitize_text_field(wp_unslash($_GET['locale'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if (FormBuilderRouteBuilder::SLUG !== $page || 'new' !== $donationFormId) {
            return;
        }

        if (!current_user_can(UserPermissions::donationForms()->editCap())) {
            return;
        }

        check_admin_referer(FormBuilderRouteBuilder::CREATE_NONCE_ACTION);

        // Make sure the Form will be created using the proper locale
        Language::switchToLocale($locale);

        $form = new DonationForm([
            'title' => __('Donation Form', 'give'),
            'status' => DonationFormStatus::DRAFT(),
            'settings' => FormSettings::fromArray([
                'enableDonationGoal' => true,
                'goalAmount' => 1000,
                'inheritCampaignColors' => true,
            ]),
            'blocks' => (new GenerateDefaultDonationFormBlockCollection())(),
        ]);

        Hooks::doAction('givewp_form_builder_new_form', $form);

        $form->save();

        wp_safe_redirect(FormBuilderRouteBuilder::makeEditFormRoute($form->id, $locale)->getUrl());
        exit();
    }
}
