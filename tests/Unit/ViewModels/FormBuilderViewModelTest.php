<?php

namespace Give\Tests\Unit\VieModels;

use Exception;
use Give\DonationForms\Actions\GenerateDonationFormPreviewRouteUrl;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\FormBuilder\DataTransferObjects\EmailNotificationData;
use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\FormBuilder\ViewModels\FormBuilderViewModel;
use Give\Framework\FormDesigns\FormDesign;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Helpers\IntlTelInput;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class FormBuilderViewModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.9.0 Add support to intlTelInputSettings key in the compared array
     * @since 3.7.0 Add support to isExcerptEnabled key in the compared array
     * @since 3.2.0 Add support to nameTitlePrefixes key in the compared array
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnStorageData()
    {
        $viewModel = new FormBuilderViewModel();
        /** @var DonationForm $mockForm */
        $mockForm = DonationForm::factory()->create();
        $formId = $mockForm->id;

        $this->assertSame(
            [
                'formId' => $formId,
                'resourceURL' => rest_url(FormBuilderRestRouteConfig::NAMESPACE . '/form/' . $formId),
                'previewURL' => (new GenerateDonationFormPreviewRouteUrl())($formId),
                'nonce' => wp_create_nonce('wp_rest'),
                'blockData' => $mockForm->blocks->toJson(),
                'settings' => $mockForm->settings->toJson(),
                'currency' => give_get_currency(),
                'formDesigns' => array_map(static function ($designClass) {
                    /** @var FormDesign $design */
                    $design = give($designClass);

                    return [
                        'id' => $design::id(),
                        'name' => $design::name(),
                        'isMultiStep' => $design->isMultiStep(),
                    ];
                }, give(FormDesignRegistrar::class)->getDesigns()),
                'formPage' => [
                    'isEnabled' => give_is_setting_enabled(give_get_option('forms_singular')),
                    // Note: Boolean values must be nested in an array to maintain boolean type, see \WP_Scripts::localize().
                    'permalink' => add_query_arg(['p' => $formId], site_url('?post_type=give_forms')),
                    'rewriteSlug' => get_post_type_object('give_forms')->rewrite['slug'],
                    'baseUrl' => preg_replace('/^https?:\/\//', '', site_url()),
                ],
                'gateways' => $viewModel->getGateways(),
                'gatewaySettingsUrl' => admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways'),
                'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION') ? GIVE_RECURRING_VERSION : null,
                'recurringAddonData' => [
                    'isInstalled' => defined('GIVE_RECURRING_VERSION'),
                ],
                'formFieldManagerData' => [
                    'isInstalled' => defined('GIVE_FFM_VERSION'),
                ],
                'emailTemplateTags' => $viewModel->getEmailTemplateTags(),
                'emailNotifications' => array_map(static function ($notification) {
                    return EmailNotificationData::fromLegacyNotification($notification);
                }, apply_filters('give_email_notification_options_metabox_fields', [], $formId)),
                'emailPreviewURL' => rest_url('givewp/form-builder/email-preview'),
                'emailDefaultAddress' => get_option('admin_email'),
                'disallowedFieldNames' => $this->getDisallowedFieldNames(),
                'donationConfirmationTemplateTags' => $viewModel->getDonationConfirmationPageTemplateTags(),
                'termsAndConditions' => [
                    'checkboxLabel' => give_get_option('agree_to_terms_label'),
                    'agreementText' => give_get_option('agreement_text'),
                ],
                'goalTypeOptions' => $viewModel->getGoalTypeOptions(),
                'goalProgressOptions' => $viewModel->getGoalProgressOptions(),
                'nameTitlePrefixes' => give_get_option('title_prefixes'),
                'isExcerptEnabled' => give_is_setting_enabled(give_get_option('forms_excerpt')),
                'intlTelInputSettings' => IntlTelInput::getSettings(),
            ],
            $viewModel->storageData($formId)
        );
    }

    private function getDisallowedFieldNames(): array
    {
        $disallowedFieldNames = array_merge(
            Donation::propertyKeys(),
            array_values(DonationMetaKeys::toArray()),
            Donor::propertyKeys(),
            array_values(DonorMetaKeys::toArray()),
            Subscription::propertyKeys(),
            [
                'fund_id',
                'login',
                'consent',
                'donation-summary',
            ]
        );

        return array_values(array_unique($disallowedFieldNames));
    }
}
