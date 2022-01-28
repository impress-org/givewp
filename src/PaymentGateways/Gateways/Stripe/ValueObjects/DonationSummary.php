<?php

namespace Give\PaymentGateways\Gateways\Stripe\ValueObjects;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * @unreleased
 */
class DonationSummary
{
    /** @var int */
    protected $length = 255;

    /** @var GatewayPaymentData */
    protected $paymentData;

    /**
     * @unreleased
     * @param GatewayPaymentData $paymentData
     */
    public function __construct( GatewayPaymentData $paymentData )
    {
        $this->paymentData = $paymentData;
    }

    /**
     * @unreleased
     * @param $length
     */
    public function setLength( $length )
    {
        $this->length = $length;
    }

    /**
     * @unreleased
     * @return string
     */
    public function getSummaryWithDonor()
    {
        return $this->trimAndFilter(implode(' - ', [
            $this->getSummary(),
            $this->getDonorLabel(),
        ]));
    }

    /**
     * @unreleased
     * @return string
     */
    public function getSummary()
    {
        return $this->trimAndFilter(implode( ': ', array_filter([
            $this->getLabel(),
            $this->getPriceLabel(),
        ])));
    }

    /**
     * @unreleased
     * @param $property
     * @return mixed|void
     */
    protected function get( $property )
    {
        if( property_exists( $this->paymentData, $property ) ) {
            return $this->paymentData->$property;
        }
    }

    /**
     * @unreleased
     * @return string
     */
    protected function getLabel()
    {
        $formId = give_get_payment_form_id( $this->get( 'donationId' ) );
        return wp_sprintf( __( 'Donation Form ID: %d', 'give' ), $formId );
    }

    /**
     * @unreleased
     * @return string
     */
    protected function getPriceLabel()
    {
        $formId = give_get_payment_form_id( $this->get( 'donationId' ) );
        return $this->get( 'priceId' )
            ? give_get_price_option_name( $formId, $this->get( 'priceId' ) )
            : '';
    }

    /**
     * @unreleased
     * @return string
     */
    protected function getDonorLabel()
    {
        return sprintf(
            '%s %s (%s)',
            $this->get( 'donorInfo' )->firstName,
            $this->get( 'donorInfo' )->lastName,
            $this->get( 'donorInfo' )->email
        );
    }

    /**
     * @unreleased
     * @param string $text
     * @return string
     */
    protected function trimAndFilter( $text )
    {
        /**
         * @since 1.8.12
         */
        return apply_filters( 'give_payment_gateway_donation_summary', substr( $text, 0, $this->length ) );
    }
}
