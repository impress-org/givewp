<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\RedirectOffsitePaymentReturnCommand;
use Give\Session\SessionDonation\DonationAccessor;

/**
 * This class use to handle RedirectOffsitePaymentReturnCommand type commands.
 *
 * @unreleased
 */
class ReturnOffsitePaymentReturnHandler
{
    /**
     * @var RedirectOffsitePaymentReturnCommand
     */
    protected $redirectOffsitePaymentReturnCommand;

    /**
     * @unreleased
     *
     * @param RedirectOffsitePaymentReturnCommand $redirectOffsitePaymentReturnCommand
     */
    public function __construct(RedirectOffsitePaymentReturnCommand $redirectOffsitePaymentReturnCommand)
    {
        $this->redirectOffsitePaymentReturnCommand = $redirectOffsitePaymentReturnCommand;
    }

    /**
     * @unreleased
     *
     * @param RedirectOffsitePaymentReturnCommand $redirectOffsitePaymentReturnCommand
     *
     * @return static
     */
    public static function make(RedirectOffsitePaymentReturnCommand $redirectOffsitePaymentReturnCommand)
    {
        return new static($redirectOffsitePaymentReturnCommand);
    }

    /**
     * @unreleased
     *
     * @param int $donationId
     *
     * @return void
     */
    public function handle($donationId)
    {
        $donationFormPageUrl = (new DonationAccessor())->get()->formEntry->currentUrl;
        $redirectURl = $this->redirectOffsitePaymentReturnCommand->getUrl($donationFormPageUrl);

        wp_redirect($redirectURl);
        exit;
    }
}
