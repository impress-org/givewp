<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\Tabs\Organization;

use Give\PaymentGateways\TheGivingBlock\Admin\Tabs\Organization\Actions\RenderOnboardingForm;
use Give\PaymentGateways\TheGivingBlock\Admin\Tabs\Organization\Actions\RenderOrganizationDetails;
use Give\PaymentGateways\TheGivingBlock\DataTransferObjects\Organization;
use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * @unreleased
 */
class OrganizationTabContent
{
    /**
     * @unreleased
     */
    public static function display()
    {
        if (OrganizationRepository::isConnected()) {
            $organization = Organization::fromOptions();
            give(RenderOrganizationDetails::class)($organization);
        } else {
            give(RenderOnboardingForm::class)();
        }
    }
}
