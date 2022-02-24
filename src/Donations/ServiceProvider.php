<?php

namespace Give\Donations;

use Give\Donations\LegacyListeners\DispatchGiveInsertPayment;
use Give\Donations\LegacyListeners\DispatchGiveUpdatePaymentStatus;
use Give\Donations\LegacyListeners\RemoveSequentialId;
use Give\Donations\LegacyListeners\SaveOrUpdateSequentialNumberingForDonation;
use Give\Donations\Repositories\DonationRepository;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donations', DonationRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->bootLegacyListeners();
    }

    /**
     * Legacy Listeners
     *
     * @unreleased
     */
    private function bootLegacyListeners()
    {
        Hooks::addAction('give_donation_updated', DispatchGiveUpdatePaymentStatus::class);
        Hooks::addAction('give_donation_updated', SaveOrUpdateSequentialNumberingForDonation::class);
        Hooks::addAction('give_donation_created', SaveOrUpdateSequentialNumberingForDonation::class);
        Hooks::addAction('give_donation_created', DispatchGiveInsertPayment::class);
        Hooks::addAction('give_donation_deleted', RemoveSequentialId::class);
    }
}
