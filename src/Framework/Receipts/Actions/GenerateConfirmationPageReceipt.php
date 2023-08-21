<?php

namespace Give\Framework\Receipts\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Concerns\HasLabel;
use Give\Framework\FieldsAPI\Concerns\HasName;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Receipts\Properties\ReceiptDetail;
use Give\Framework\TemplateTags\DonationTemplateTags;

class GenerateConfirmationPageReceipt
{
    /**
     * @since 3.0.0
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
     * @since 3.0.0
     */
    protected function getCustomFields(Donation $donation): array
    {
        if (give(DonationFormRepository::class)->isLegacyForm($donation->formId)) {
            return [];
        }

        /** @var DonationForm $form */
        $form = DonationForm::find($donation->formId);

        $customFields = array_filter($form->schema()->getFields(), static function (Field $field) {
            return $field->shouldShowInReceipt();
        });

        $receiptDetails = [];
        foreach($customFields as $field) {
            /** @var Field|HasLabel|HasName $field */
            if ($field->shouldStoreAsDonorMeta()) {
                if (!metadata_exists('donor', $donation->donor->id, $field->getName()) ) {
                    continue;
                }

                $value = give()->donor_meta->get_meta($donation->donor->id, $field->getName(), true);
            } else {
                if (!metadata_exists('donation', $donation->id, $field->getName()) ) {
                    continue;
                }

                $value = give()->payment_meta->get_meta($donation->id, $field->getName(), true);
            }

            $receiptDetails[] = new ReceiptDetail(
                $field->getLabel(),
                $value
            );
        }

        return $receiptDetails;
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
     *
     * @return void
     */
    private function fillDonorDetails(DonationReceipt $receipt)
    {
        $details = [
            new ReceiptDetail(
                __('Donor Name', 'give'),
                trim("{$receipt->donation->firstName} {$receipt->donation->lastName}")
            ),
            new ReceiptDetail(
                __('Email Address', 'give'),
                $receipt->donation->email
            ),
        ];

        if ($receipt->donation->billingAddress->country) {
            $details[] = new ReceiptDetail(
                __('Billing Address', 'give'),
                $receipt->donation->billingAddress->address1 . ' ' . $receipt->donation->billingAddress->address2 . PHP_EOL .
                $receipt->donation->billingAddress->city . ($receipt->donation->billingAddress->state ? ', ' . $receipt->donation->billingAddress->state : '') . ' ' . $receipt->donation->billingAddress->zip . PHP_EOL .
                $receipt->donation->billingAddress->country . PHP_EOL);
        }

        $receipt->donorDetails->addDetails($details);
    }

    /**
     * @since 3.0.0
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

        if ($receipt->donation->comment) {
            $receipt->additionalDetails->addDetail(
                new ReceiptDetail(
                    __('Comment', 'give'),
                    $receipt->donation->comment
                )
            );
        }

        if ($receipt->donation->anonymous) {
            $receipt->additionalDetails->addDetail(
                new ReceiptDetail(
                    __('Anonymous Donation', 'give'),
                    'Yes'
                )
            );
        }

        if ($customFields = $this->getCustomFields($receipt->donation)) {
            $receipt->additionalDetails->addDetails($customFields);
        }
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
     */
    private function fillSettings(DonationReceipt $receipt)
    {
        $donationForm = $this->getDonationForm($receipt->donation->formId);

        $receipt->settings->addSetting(
            'heading',
            nl2br($this->getHeading($receipt, $donationForm))
        );

        $receipt->settings->addSetting(
            'description',
            nl2br($this->getDescription($receipt, $donationForm))
        );

        $receipt->settings->addSetting('currency', $receipt->donation->amount->getCurrency()->getCode());
        $receipt->settings->addSetting('donorDashboardUrl', get_permalink(give_get_option('donor_dashboard_page')));

        $receipt->settings->addSetting(
            'pdfReceiptLink',
            apply_filters('givewp_confirmation_page_receipt_settings_pdfReceiptLink', '', $receipt)
        );
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
     */
    protected function getHeading(DonationReceipt $receipt, DonationForm $donationForm = null): string
    {
        if (!$donationForm) {
            $content = __("Hey {first_name}, thanks for your donation!", 'give');
        } else {
            $content = $donationForm->settings->receiptHeading;
        }

        return $this->transformV2FormTags(
            (new DonationTemplateTags($receipt->donation, $content))->getContent(),
            $receipt->donation
        );
    }

    /**
     * @since 3.0.0
     */
    protected function getDescription(DonationReceipt $receipt, DonationForm $donationForm = null): string
    {
        if (!$donationForm) {
            $content = __(
                "{first_name}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {email}.",
                'give'
            );
        } else {
            $content = $donationForm->settings->receiptDescription;
        }

        return $this->transformV2FormTags(
            (new DonationTemplateTags($receipt->donation, $content))->getContent(),
            $receipt->donation
        );
    }

    /**
     * @since 3.0.0
     */
    protected function transformV2FormTags(string $content, Donation $donation): string
    {
        return give_do_email_tags($content, ['payment_id' => $donation->id, 'form_id' => $donation->formId]
        );
    }
}
