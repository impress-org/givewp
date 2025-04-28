<?php

namespace Give\PaymentGateways\Stripe;

use Give\License\Repositories\LicenseRepository;
use Give\PaymentGateways\Stripe\Models\AccountDetail as AccountDetailModel;

/**
 * Class ApplicationFee
 * @package Give\PaymentGateways\Stripe
 *
 * @see https://github.com/impress-org/givewp/issues/5555#issuecomment-759596226
 *
 * @since 2.10.2
 */
class ApplicationFee
{
    protected AccountDetailModel $accountDetail;

    protected LicenseRepository $licenseRepository;

    /**
     * @unreleased added LicenseRepository
     * @since 2.10.2
     */
    public function __construct(AccountDetailModel $accountDetail, LicenseRepository $licenseRepository)
    {
        $this->accountDetail = $accountDetail;
        $this->licenseRepository = $licenseRepository;
    }

    /**
     * Returns true or false based on whether the Stripe fee should be applied or not
     *
     * @unreleased updated logic to check license for gateway fee
     * @since 2.10.2
     */
    public static function canAddFee(): bool
    {
        /* @var self $gate */
        $gate = give(static::class);

        if (!$gate->doesCountrySupportApplicationFee()) {
            return false;
        }

        return $gate->licenseRepository->getGatewayFeePercentage() > 0;
    }

    /**
     * Return whether country support application fee.
     *
     * @since 2.10.2
     */
    public function doesCountrySupportApplicationFee(): bool
    {
        return 'BR' !== $this->accountDetail->accountCountry;
    }
}
