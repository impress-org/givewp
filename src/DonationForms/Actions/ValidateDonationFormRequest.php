<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\DataTransferObjects\ValidationRouteData;
use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\DonationForms\Exceptions\DonationFormForbidden;
use Give\Helpers\Form\Utils as FormUtils;
use WP_Error;

/**
 * Holds a donation form request to the form's own validation rules before anything else acts on it.
 *
 * Gateways that must talk to their processor before the donate route runs (PayPal Commerce creates
 * its order when the PayPal button is clicked) call this so the request meets the same rules form
 * processing applies, without the gateway learning anything about form configuration.
 *
 * Visual Form Builder (v3) forms validate through ValidationRouteData, the same DTO the validate
 * route uses: every schema field present in the request is checked, security challenge fields are
 * skipped, and trashed or unpublished forms are refused. Option-based (v2) forms run the legacy
 * validator give_donation_form_validate_fields() and then fire give_checkout_error_checks, the same
 * sequence give_process_donation_form() runs before it creates a payment, so the level check and
 * add-on rules hooked there apply too. The legacy validator reads $_POST directly, so for v2 forms
 * the request must be the current POST request.
 *
 * @since 4.16.7.1
 */
class ValidateDonationFormRequest
{
    /**
     * @since 4.16.7.1
     *
     * @param int $formId The form the caller already verified. For v3 forms it overrides any formId
     *                    in the request; for v2 forms the request's give-form-id must match it.
     * @param array $request The request values, keyed by field name.
     *
     * @throws DonationFormFieldErrorsException|DonationFormForbidden
     */
    public function __invoke(int $formId, array $request): void
    {
        if (FormUtils::isV3Form($formId)) {
            $request['formId'] = $formId;

            ValidationRouteData::fromRequest($request)->validate();

            return;
        }

        $this->validateLegacyRequest($formId);
    }

    /**
     * The legacy validator reports through the session error store, so it is cleared before and
     * after the run: before, so an earlier failed attempt in the same session cannot bleed in;
     * after, because the errors now live on the exception.
     *
     * @since 4.16.7.1
     *
     * @throws DonationFormFieldErrorsException
     */
    private function validateLegacyRequest(int $formId): void
    {
        if (absint($_POST['give-form-id'] ?? 0) !== $formId) {
            throw new DonationFormFieldErrorsException(
                new WP_Error(
                    'give_invalid_donation_form',
                    __('The donation form ID is invalid. Please reload the page and try again.', 'give')
                )
            );
        }

        give_clear_errors();
        $validData = give_donation_form_validate_fields();

        /** This action is documented in includes/process-donation.php */
        do_action('give_checkout_error_checks', $validData, give_clean($_POST));

        $errors = give_get_errors();

        give_clear_errors();

        if (!$errors) {
            return;
        }

        $wpError = new WP_Error();

        foreach ($errors as $errorId => $error) {
            $wpError->add($errorId, is_array($error) ? $error['message'] : $error);
        }

        throw new DonationFormFieldErrorsException($wpError);
    }
}
