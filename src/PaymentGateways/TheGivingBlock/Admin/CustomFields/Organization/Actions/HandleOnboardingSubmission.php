<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions;

use Give\PaymentGateways\TheGivingBlock\API\TheGivingBlockApi;
use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * @unreleased
 */
class HandleOnboardingSubmission
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'giveTgbNonce')) {
            wp_send_json_error(['message' => __('Invalid nonce. Please refresh the page and try again.', 'give')]);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'give')]);
        }

        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $ein = sanitize_text_field(wp_unslash($_POST['ein'] ?? ''));
        $address1 = sanitize_text_field(wp_unslash($_POST['address1'] ?? ''));
        $address2 = sanitize_text_field(wp_unslash($_POST['address2'] ?? ''));
        $city = sanitize_text_field(wp_unslash($_POST['city'] ?? ''));
        $state = sanitize_text_field(wp_unslash($_POST['state'] ?? ''));
        $postcode = sanitize_text_field(wp_unslash($_POST['postcode'] ?? ''));
        $contact_email = sanitize_email(wp_unslash($_POST['contactEmail'] ?? ''));
        $website_url = esc_url_raw(wp_unslash($_POST['websiteUrl'] ?? ''));

        $is_website_profile_visible = isset($_POST['isWebsiteProfileVisible']);

        if (empty($name) || empty($ein) || empty($address1) ||
            empty($city) || empty($state) || empty($postcode) ||
            empty($contact_email)) {
            wp_send_json_error(['message' => __('Please fill in all required fields.', 'give')]);
        }

        if (!preg_match('/^\d{5}$/', $postcode)) {
            wp_send_json_error(['message' => __('Postal code must be exactly 5 digits (e.g., 12345).', 'give')]);
        }

        $organizationData = [
            'name' => $name,
            'ein' => $ein,
            'address1' => $address1,
            'city' => $city,
            'state' => $state,
            'postcode' => $postcode,
            'contactEmail' => $contact_email,
            'onboardingSource' => 'givewp', // Fixed value as required by the API
        ];

        if (!empty($address2)) {
            $organizationData['address2'] = $address2;
        }

        if (!empty($website_url)) {
            $organizationData['websiteUrl'] = $website_url;
        }

        if ($is_website_profile_visible) {
            $organizationData['isWebsiteProfileVisible'] = true;
        }

        $response = TheGivingBlockApi::nonProfitOnboarding($organizationData);

        if (!is_array($response) || !isset($response['code'])) {
            wp_send_json_error(['message' => __('Unexpected response from onboarding API.', 'give')]);
        }

        $code = $response['code'];
        $data = $response['data'];

        if ($code === 200 || $code === 201) {
            $warnings = [];
            $organizationId = $data['data']['organizationId'] ?? null;
            if ($organizationId) {
                $organizationDetailsResponse = TheGivingBlockApi::getOrganizationById($organizationId);
                if (is_array($organizationDetailsResponse) && $organizationDetailsResponse['code'] === 200) {
                    $organizationData = $organizationDetailsResponse['data']['data']['organization'] ?? null;
                    if ($organizationData) {
                        OrganizationRepository::save($organizationData);
                    }
                }

                $cryptoOnboardingResponse = TheGivingBlockApi::nonProfitCryptoOnboarding($organizationId);
                if (!is_array($cryptoOnboardingResponse) || !in_array($cryptoOnboardingResponse['code'], [200, 201], true)) {
                    $warnings[] = __('Crypto onboarding could not be completed.', 'give');
                }

                $stockOnboardingResponse = TheGivingBlockApi::nonProfitStockOnboarding($organizationId);
                if (!is_array($stockOnboardingResponse) || !in_array($stockOnboardingResponse['code'], [200, 201], true)) {
                    $warnings[] = __('Stock onboarding could not be completed.', 'give');
                }
            }
            wp_send_json_success([
                'message' => __('Organization has been successfully submitted for onboarding! Refreshing page, wait...', 'give'),
                'reload' => empty($warnings),
                'warnings' => $warnings,
            ]);
        } elseif ($code === 409) {
            $organizationId = $data['data']['meta']['organizationId'] ?? null;

            if ($organizationId) {
                $organizationResponse = TheGivingBlockApi::getOrganizationById($organizationId);

                if (is_array($organizationResponse) && ($organizationResponse['code'] ?? 0) === 200) {
                    $existingOrg = $organizationResponse['data']['data']['organization'] ?? null;

                    if ($existingOrg && $this->isSameOrganization($existingOrg, $organizationData)) {
                        OrganizationRepository::save($organizationData);
                        wp_send_json_success([
                            'message' => __('Organization already exists and has been linked successfully! Refreshing page, wait...', 'give'),
                            'reload' => true
                        ]);
                    }
                }
            }

            $errorMessage = isset($data['data']['errorMessage']) ? $data['data']['errorMessage'] : __('Organization with same EIN already exists', 'give');
            wp_send_json_error(['message' => $errorMessage]);
        } else {
            $error_message = '';

            // First, try to get error from direct data structure
            if (isset($data['data']['meta']['validationErrorMessage'])) {
                $error_message = $data['data']['meta']['validationErrorMessage'];
            } elseif (isset($data['data']['errorMessage'])) {
                $error_message = $data['data']['errorMessage'];
            }

            // If empty, try to extract from nested JSON in data['error']
            if (empty($error_message) && isset($data['error']) && is_string($data['error'])) {
                // Try to find and parse JSON within the error string
                if (preg_match('/\{.*\}/s', $data['error'], $matches)) {
                    $nestedJson = json_decode($matches[0], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($nestedJson)) {
                        // Check for errorMessage and validationErrorMessage in nested structure
                        if (isset($nestedJson['data']['meta']['validationErrorMessage'])) {
                            $error_message = $nestedJson['data']['meta']['validationErrorMessage'];
                        } elseif (isset($nestedJson['data']['errorMessage'])) {
                            $error_message = $nestedJson['data']['errorMessage'];
                        }
                    }
                }
            }

            // If still empty, try to extract from response body
            if (empty($error_message) && isset($response['body']) && is_string($response['body'])) {
                $bodyData = json_decode($response['body'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($bodyData)) {
                    // Check if body contains error with nested JSON
                    if (isset($bodyData['error']) && is_string($bodyData['error'])) {
                        if (preg_match('/\{.*\}/s', $bodyData['error'], $matches)) {
                            $nestedJson = json_decode($matches[0], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($nestedJson)) {
                                if (isset($nestedJson['data']['meta']['validationErrorMessage'])) {
                                    $error_message = $nestedJson['data']['meta']['validationErrorMessage'];
                                } elseif (isset($nestedJson['data']['errorMessage'])) {
                                    $error_message = $nestedJson['data']['errorMessage'];
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($error_message)) {
                if (strpos($error_message, 'Candid verification status') !== false) {
                    $detailedMessage = '<strong>Verification Issue:</strong> The organization could not be onboarded due to Candid verification status.<br><br>' .
                                     'This means the organization may not be properly registered or verified in the Candid database (formerly GuideStar).<br><br>' .
                                     '<strong>Possible solutions:</strong><br>' .
                                     '• Ensure the organization is properly registered as a 501(c)(3) nonprofit<br>' .
                                     '• Verify the EIN is correct and active<br>' .
                                     '• Check if the organization exists in the <a href="https://candid.org/" target="_blank" rel="nofollow noopener noreferrer" style="color: #0073aa;">Candid database</a> (formerly GuideStar)<br>' .
                                     '• Contact <a href="https://thegivingblock.com/about/contact/" target="_blank" rel="noopener noreferrer" style="color: #0073aa;">The Giving Block support</a> for assistance';

                    wp_send_json_error(['message' => $detailedMessage]);
                } else {
                    wp_send_json_error(['message' => $error_message]);
                }
            } else {
                wp_send_json_error([
                    'message' => __('Unknown error occurred', 'give'),
                    'response' => $response
                ]);
            }
        }
    }

    /**
     * Check if the existing organization matches the submitted data
     *
     * @unreleased
     *
     * @param array $existingOrg The organization data from API
     * @param array $submittedData The data submitted in the form
     * @return bool True if it's the same organization
     */
    private function isSameOrganization($existingOrg, $submittedData)
    {
        $fieldsToCompare = [
            'name' => 'name',
            'ein' => 'nonprofitTaxID',
            'address1' => 'nonprofitAddress1',
            'city' => 'city',
            'state' => 'state',
            'postcode' => 'postcode'
        ];

        foreach ($fieldsToCompare as $submittedKey => $existingKey) {
            $submittedValue = trim($submittedData[$submittedKey] ?? '');
            $existingValue = trim($existingOrg[$existingKey] ?? '');

            if (empty($submittedValue)) {
                continue;
            }

            if (strtolower($submittedValue) !== strtolower($existingValue)) {
                return false;
            }
        }

        return true;
    }
}
