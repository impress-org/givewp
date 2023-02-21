<?php

namespace Give\Receipt;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give_Payment;

class DonationReceipt extends Receipt
{
    /**
     * Receipt donor section id.
     */
    const DONORSECTIONID = 'Donor';

    /**
     * Receipt donation section id.
     */
    const DONATIONSECTIONID = 'Donation';

    /**
     * Receipt additional information section id.
     */
    const ADDITIONALINFORMATIONSECTIONID = 'AdditionalInformation';

    /**
     * Donation id.
     *
     * @since 2.7.0
     * @var int $donationId
     */
    public $donationId;

    /**
     * @var Give_Payment
     * @since 2.18.0
     */
    protected $donation;

    /**
     * Receipt constructor.
     *
     * @param $donationId
     *
     * @since 2.7.0
     */
    public function __construct($donationId)
    {
        $this->donationId = $donationId;
        $this->donation   = new Give_Payment($donationId);

        $this->addDonorSection();
        $this->addDonationSection();
        $this->addAdditionalInformationSection();
    }

    /**
     * Add donor section.
     *
     * @since 2.7.0
     */
    private function addDonorSection()
    {
        $donorSection = $this->addSection([
            'id'    => self::DONORSECTIONID,
            'label' => esc_html__('Donor Details', 'give'),
        ]);

        $donorSection->addLineItem([
            'id'    => 'fullName',
            'label' => esc_html__('Donor Name', 'give'),
            'value' => trim("{$this->donation->first_name} {$this->donation->last_name}"),
            'icon'  => '<i class="fas fa-user"></i>',
        ]);

        $donorSection->addLineItem([
            'id'    => 'emailAddress',
            'label' => esc_html__('Email Address', 'give'),
            'value' => $this->donation->email,
            'icon'  => '<i class="fas fa-envelope"></i>',
        ]);

        if ($address = $this->getDonorBillingAddress()) {
            $donorSection->addLineItem([
                'id'    => 'billingAddress',
                'label' => esc_html__('Billing Address', 'give'),
                'value' => $address,
                'icon'  => '<i class="fas fa-globe-americas"></i>',
            ]);
        }
    }

    /**
     * Add donation section.
     *
     * @since 2.24.0 Add notice for donations with the "processing" status
     * @since      2.7.0
     */
    private function addDonationSection()
    {
        $donationSection = $this->addSection([
            'id' => self::DONATIONSECTIONID,
            'label' => esc_html__('Donation Details', 'give'),
        ]);

        $donationSection->addLineItem([
            'id' => 'paymentStatus',
            'label' => esc_html__('Payment Status', 'give'),
            'value' => give_get_payment_statuses()[$this->donation->post_status],
        ]);

        if (DonationStatus::PROCESSING()->getValue() === $this->donation->post_status) {
            $donationSection->addLineItem([
                'id' => 'processingStatusNotice',
                'label' => '<div style="text-transform:initial;font-size:0.73rem;color:#3398DB;display:flex;flex-flow:row nowrap;white-space:pre-wrap;margin-top:-0.5rem;">' .
                           esc_html__('You will receive a final receipt in your email once it has been completed.',
                               'give') .
                           '</div>',
                'value' => '⠀',
            ]);
        }

        $donationSection->addLineItem([
            'id' => 'paymentMethod',
            'label' => esc_html__('Payment Method', 'give'),
            'value' => give_get_gateway_checkout_label($this->donation->gateway),
        ]);

        $donationSection->addLineItem([
            'id' => 'amount',
            'label' => esc_html__('Donation Amount', 'give'),
            'value' => give_currency_filter(
                give_format_amount($this->donation->total, ['donation_id' => $this->donation->ID]),
                [
                    'currency_code' => $this->donation->currency,
                    'form_id' => $this->donation->form_id,
                    'decode_currency' => true,
                ]
            ),
        ]);

        $donationSection->addLineItem([
            'id'    => 'totalAmount',
            'label' => esc_html__('Donation Total', 'give'),
            'value' => give_currency_filter(
                give_format_amount($this->donation->total, ['donation_id' => $this->donation->ID]),
                [
                    'currency_code' => $this->donation->currency,
                    'form_id' => $this->donation->form_id,
                    'decode_currency' => true,
                ]
            ),
        ]);
    }

    /**
     *  Add Additional Information Section
     */
    private function addAdditionalInformationSection()
    {
        $this->addSection([
            'id'    => self::ADDITIONALINFORMATIONSECTIONID,
            'label' => esc_html__('Additional Information', 'give'),
        ]);
    }

    /**
     * Get donor billing address
     *
     * @return string|null
     */
    private function getDonorBillingAddress()
    {
        $address   = give_get_donation_address($this->donationId);
        $formatted = sprintf(
            '%1$s%7$s%2$s%3$s, %4$s%5$s%7$s%6$s',
            $address['line1'],
            ! empty($address['line2']) ? $address['line2'] . "\r\n" : '',
            $address['city'],
            $address['state'],
            $address['zip'],
            $address['country'],
            "\r\n"
        );

        $hasAddress = (bool) trim(str_replace(',', '', strip_tags($formatted)));

        if ($hasAddress) {
            return $formatted;
        }

        return null;
    }

    /**
     * Set iterator position to zero when rewind.
     *
     * @since 2.7.0
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Validate section.
     *
     * @param  array  $array
     *
     * @since 2.7.0
     */
    protected function validateSection($array)
    {
        $required = [ 'id' ];
        $array    = array_filter($array); // Remove empty values.

        if (array_diff($required, array_keys($array))) {
            throw new InvalidArgumentException(esc_html__('Invalid receipt section. Please provide valid section id', 'give'));
        }
    }
}
