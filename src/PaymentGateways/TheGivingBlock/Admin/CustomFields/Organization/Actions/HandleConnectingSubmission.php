<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions;

use Give\PaymentGateways\TheGivingBlock\API\TheGivingBlockApi;
use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * @unreleased
 */
class HandleConnectingSubmission
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

        $organizationId = sanitize_text_field(wp_unslash($_POST['organizationId'] ?? ''));

        if (empty($organizationId)) {
            wp_send_json_error(['message' => __('Organization ID is required.', 'give')]);
        }

        if (!is_numeric($organizationId)) {
            wp_send_json_error(['message' => __('Organization ID must be a valid number.', 'give')]);
        }

        $organizationResponse = TheGivingBlockApi::getOrganizationById($organizationId);

        if (!is_array($organizationResponse) || !isset($organizationResponse['code'])) {
            wp_send_json_error(['message' => __('Unexpected response from organization API.', 'give')]);
        }

        $code = $organizationResponse['code'];
        $data = $organizationResponse['data'];

        if ($code === 200 && isset($data['data']['organization'])) {
            OrganizationRepository::save($data['data']['organization']);

            $warnings = [];
            $organizationIdNumeric = (string) $organizationId;

            $cryptoOnboardingResponse = TheGivingBlockApi::nonProfitCryptoOnboarding($organizationIdNumeric);
            if (!is_array($cryptoOnboardingResponse) || !in_array($cryptoOnboardingResponse['code'], [200, 201], true)) {
                $warnings[] = __('Crypto onboarding could not be completed.', 'give');
            }

            $stockOnboardingResponse = TheGivingBlockApi::nonProfitStockOnboarding($organizationIdNumeric);
            if (!is_array($stockOnboardingResponse) || !in_array($stockOnboardingResponse['code'], [200, 201], true)) {
                $warnings[] = __('Stock onboarding could not be completed.', 'give');
            }

            wp_send_json_success([
                'message' => __('Organization connected successfully! Refreshing page, wait...', 'give'),
                'reload' => empty($warnings),
                'warnings' => $warnings,
            ]);
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
            if (empty($error_message) && isset($organizationResponse['body']) && is_string($organizationResponse['body'])) {
                $bodyData = json_decode($organizationResponse['body'], true);
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
                wp_send_json_error(['message' => $error_message]);
            } else {
                wp_send_json_error([
                    'message' => __('Organization not found. Please check your Organization ID.', 'give'),
                    'response' => $organizationResponse
                ]);
            }
        }
    }
}
