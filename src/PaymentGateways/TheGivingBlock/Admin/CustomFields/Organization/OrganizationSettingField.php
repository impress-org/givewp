<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization;

use Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions\RenderOnboardingForm;
use Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions\RenderOrganizationDetails;
use Give\PaymentGateways\TheGivingBlock\DataTransferObjects\Organization;
use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * Custom setting field for GiveWP > Payment Gateways > The Giving Block > Organization.
 *
 * @unreleased
 */
class OrganizationSettingField
{
    /**
     * Render the Organization field content.
     *
     * @unreleased
     *
     * @param array $field Field config (id, type, wrapper_class, etc.).
     */
    public function handle(array $field): void
    {
        if (OrganizationRepository::isConnected()) {
            $organization = Organization::fromOptions();
            give(RenderOrganizationDetails::class)($organization);
        } else {
            give(RenderOnboardingForm::class)();
        }
    }
}
