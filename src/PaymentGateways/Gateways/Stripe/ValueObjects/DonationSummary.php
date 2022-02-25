<?php

namespace Give\PaymentGateways\Gateways\Stripe\ValueObjects;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * @since 2.19.0
 */
class DonationSummary
{
    /** @var int */
    protected $length = 255;

    /** @var GatewayPaymentData */
    protected $paymentData;

    /**
     * @since 2.19.0
     * @param GatewayPaymentData $paymentData
     */
    public function __construct( GatewayPaymentData $paymentData )
    {
        $this->paymentData = $paymentData;
    }

    /**
     * @since 2.19.0
     * @param $length
     */
    public function setLength( $length )
    {
        $this->length = $length;
    }

    /**
     * @since 2.19.0
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
     * @since 2.19.0
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
     * @since 2.19.0
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
     * @since 2.19.0
     * @return string
     */
    protected function getLabel()
    {
        $formId = give_get_payment_form_id( $this->get( 'donationId' ) );
        return wp_sprintf( __( 'Donation Form ID: %d', 'give' ), $formId );
    }

    /**
     * @since 2.19.0
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
     * @since 2.19.0
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
     * @since 2.19.0
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
