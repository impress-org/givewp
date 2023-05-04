<?php

namespace Give\NextGen\Framework\Receipts\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Concerns\HasLabel;
use Give\Framework\FieldsAPI\Concerns\HasName;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\Framework\Receipts\DonationReceipt;
use Give\NextGen\Framework\Receipts\Properties\ReceiptDetail;
use Give\NextGen\Framework\TemplateTags\DonationTemplateTags;

class GenerateConfirmationPageReceipt
{
    /**
     * @since 0.1.0
     */
    public function __invoke(DonationReceipt $receipt): DonationReceipt
    {
        $this->fillSettings($receipt);
        $this->fillDonorDetails($receipt);
        $this->fillDonationDetails($receipt);
        $this->fillSubscriptionDetails($receipt);
        $this->fillAdditionalDetails($receipt);

        return $receipt;
    }

    /**
     * @since 0.1.0
     */
    protected function getCustomFields(Donation $donation): array
    {
        if (give(DonationFormRepository::class)->isLegacyForm($donation->formId)) {
            return [];
        }

        /** @var DonationForm $form */
        $form = DonationForm::find($donation->formId);

        $customFields = array_filter($form->schema()->getFields(), static function (Field $field) {
            /** $field->shouldDisplayInReceipt is a temporary macro */
            return $field->shouldDisplayInReceipt();
        });

        return array_map(static function (Field $field) use ($donation) {
            /** @var Field|HasLabel|HasName $field */
            if ($field->shouldStoreAsDonorMeta()) {
                $value = give()->donor_meta->get_meta($donation->donor->id, $field->getName(), true);
            } else {
                $value = give()->payment_meta->get_meta($donation->id, $field->getName(), true);
            }

            return new ReceiptDetail(
                $field->getLabel(),
                $value
            );
        }, $customFields);
    }

    /**
     * @since 0.1.0
     *
     * @return void
     */
    private function fillDonationDetails(DonationReceipt $receipt)
    {
        /** @var PaymentGatewayRegister $paymentGatewayRegistrar */
        $paymentGatewayRegistrar = give(PaymentGatewayRegister::class);

        $receipt->donationDetails->addDetails([
                new ReceiptDetail(
                    __('Payment Status', 'give'),
                    $receipt->donation->status->label()
                ),
                new ReceiptDetail(
                    __('Payment Method', 'give'),
                    $paymentGatewayRegistrar->hasPaymentGateway(
                        $receipt->donation->gatewayId
                    ) ? $receipt->donation->gateway()->getPaymentMethodLabel() : $receipt->donation->gatewayId
                ),
                new ReceiptDetail(
                    __('Donation Amount', 'give'),
                    ['amount' => $receipt->donation->intendedAmount()->formatToDecimal()]
                ),
            ]
        );

        if ($receipt->donation->feeAmountRecovered) {
            $receipt->donationDetails->addDetail(
                new ReceiptDetail(
                    __('Processing Fee', 'give'),
                    ['amount' => $receipt->donation->feeAmountRecovered->formatToDecimal()]
                )
            );
        }

        $receipt->donationDetails->addDetail(
            new ReceiptDetail(
                __('Donation Total', 'give'),
                ['amount' => $receipt->donation->amount->formatToDecimal()]
            )
        );
    }

    /**
     * @since 0.1.0
     *
     * @return void
     */
    private function fillDonorDetails(DonationReceipt $receipt)
    {
        $receipt->donorDetails->addDetails(
            [
                new ReceiptDetail(
                    __('Donor Name', 'give'),
                    trim("{$receipt->donation->firstName} {$receipt->donation->lastName}")
                ),
                new ReceiptDetail(
                    __('Email Address', 'give'),
                    $receipt->donation->email
                ),
            ]
        );
    }

    /**
     * @since 0.1.0
     *
     * @return void
     */
    private function fillAdditionalDetails(DonationReceipt $receipt)
    {
        if ($receipt->donation->company) {
            $receipt->additionalDetails->addDetail(
                new ReceiptDetail(
                    __('Company Name', 'give'),
                    $receipt->donation->company
                )
            );
        }

        if ($customFields = $this->getCustomFields($receipt->donation)) {
            $receipt->additionalDetails->addDetails($customFields);
        }
    }

    /**
     * @since 0.3.3 update subscription amount label with frequency
     * @since 0.1.0
     *
     * @return void
     */
    private function fillSubscriptionDetails(DonationReceipt $receipt)
    {
        if ($receipt->donation->subscriptionId) {
            $subscription = $receipt->donation->subscription;
            $subscriptionAmountLabel = sprintf(
                $subscription->period->label($subscription->frequency),
                $subscription->frequency
            );

            $receipt->subscriptionDetails->addDetails([
                new ReceiptDetail(
                    __('Subscription', 'give'),
                    [
                        'amount' =>
                            sprintf(
                                '%s / %s',
                                $subscription->amount->formatToDecimal(),
                                $subscriptionAmountLabel
                            )
                    ]
                ),
                new ReceiptDetail(
                    __('Subscription Status', 'give'),
                    $subscription->status->label()
                ),
                new ReceiptDetail(
                    __('Renewal Date', 'give'),
                    $subscription->renewsAt->format('F j, Y')
                ),
                new ReceiptDetail(
                    __('Progress', 'give'),
                    sprintf(
                        '%s / %s',
                        count($subscription->donations),
                        $subscription->installments > 0 ? $subscription->installments : __('Ongoing', 'give')
                    )
                ),
            ]);
        }
    }

    /**
     * @since 0.1.0
     */
    private function fillSettings(DonationReceipt $receipt)
    {
        $donationForm = $this->getDonationForm($receipt->donation->formId);

        $receipt->settings->addSetting(
            'heading',
            $this->getHeading($receipt, $donationForm)
        );

        $receipt->settings->addSetting(
            'description',
            $this->getDescription($receipt, $donationForm)
        );

        $receipt->settings->addSetting('currency', $receipt->donation->amount->getCurrency()->getCode());
        $receipt->settings->addSetting('donorDashboardUrl', get_permalink(give_get_option('donor_dashboard_page')));
    }

    /**
     * @since 0.1.0
     *
     * @param  int  $formId
     * @return DonationForm|null
     */
    protected function getDonationForm(int $formId)
    {
        if (give(DonationFormRepository::class)->isLegacyForm($formId)) {
            return null;
        }

        return DonationForm::find($formId);
    }

    /**
     * @since 0.1.0
     */
    protected function getHeading(DonationReceipt $receipt, DonationForm $donationForm = null): string
    {
        if (!$donationForm) {
            $content = __("Hey {donation.firstName}, thanks for your donation!", 'give');
        } else {
            $content = $donationForm->settings->receiptHeading;
        }

        return (new DonationTemplateTags($receipt->donation, $content))->getContent();
    }

    /**
     * @since 0.1.0
     */
    protected function getDescription(DonationReceipt $receipt, DonationForm $donationForm = null): string
    {
        if (!$donationForm) {
            $content = __(
                "{donation.firstName}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {donation.email}.",
                'give'
            );
        } else {
            $content = $donationForm->settings->receiptDescription;
        }

        return (new DonationTemplateTags($receipt->donation, $content))->getContent();
    }
}