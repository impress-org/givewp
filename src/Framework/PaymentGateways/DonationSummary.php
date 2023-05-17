<?php

namespace Give\Framework\PaymentGateways;

use Give\Donations\Models\Donation;

/**
 * @since 2.19.0
 */
class DonationSummary
{
    /** @var int */
    protected $length = 255;

    /** @var Donation */
    protected $donation;

    /**
     * @since 2.19.0
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    /**
     * @since 2.19.0
     *
     * @param int $length
     */
    public function setLength(int $length)
    {
        $this->length = $length;
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    public function getSummaryWithDonor(): string
    {
        return $this->trim(
            implode(' - ', [
                $this->getSummary(),
                $this->getDonorLabel(),
            ])
        );
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    public function getSummary(): string
    {
        return $this->trimAndFilter(
            implode(
                ': ',
                array_filter([
                    $this->getLabel(),
                    $this->getPriceLabel(),
                ])
            )
        );
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    protected function getLabel(): string
    {
        $formId = give_get_payment_form_id($this->donation->id);
        $formTitle = get_the_title($formId);
        return $formTitle ?: sprintf(__('Donation Form ID: %d', 'give'), $formId);
    }

    /**
     * @since 2.19.0
     * @return string
     */
    protected function getPriceLabel(): string
    {
        $priceId = $this->donation->levelId;

        return is_numeric($priceId)
            ? give_get_price_option_name($this->donation->formId, $this->donation->levelId)
            : '';
    }

    /**
     * @since 2.19.0
     */
    protected function getDonorLabel(): string
    {
        return sprintf(
            '%s %s (%s)',
            $this->donation->firstName,
            $this->donation->lastName,
            $this->donation->email
        );
    }

    /**
     * @since 2.19.0
     *
     * @param string $text
     *
     * @return string
     */
    protected function trimAndFilter(string $text): string
    {
        /**
         * @since 2.25.0 Re-use trim method for text.
         * @since 1.8.12
         */
        return apply_filters('give_payment_gateway_donation_summary', $this->trim($text));
    }

    /**
     * @since 2.25.0
     *
     * @param string $text
     *
     * @return string
     */
    protected function trim(string $text): string
    {
        return substr($text, 0, $this->length);
    }
}
