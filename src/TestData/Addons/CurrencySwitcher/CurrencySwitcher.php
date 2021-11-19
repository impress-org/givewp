<?php

namespace Give\TestData\Addons\CurrencySwitcher;

use Exception;
use Give\TestData\Framework\MetaRepository;

class CurrencySwitcher
{

    /**
     * @param $currency
     *
     * @return float
     */
    public function getCurrencyExchangeRate($currency)
    {
        $rates = give_get_option('cs_exchange_rates', []);

        if (array_key_exists($currency, $rates)) {
            return $rates[$currency]['exchange_rate'];
        }

        return 0.0;
    }

    /**
     * @param int $donationID
     * @param array $donation
     */
    public function addDonationCurrencyMeta($donationID, $donation)
    {
        // Bail out if donation currency is equal to default currency
        if ($donation['payment_currency'] === give_get_currency()) {
            return;
        }

        global $wpdb;

        // Start DB transaction
        $wpdb->query('START TRANSACTION');

        try {
            $exchangeRate = $this->getCurrencyExchangeRate($donation['payment_currency']);
            $baseAmount = round($donation['payment_total'] / $exchangeRate, 6);

            // Update donation meta
            $metaRepository = new MetaRepository('give_donationmeta', 'donation_id');
            $metaRepository->persist(
                $donationID,
                [
                    '_give_cs_base_currency' => give_get_currency(),
                    '_give_cs_exchange_rate' => $exchangeRate,
                    '_give_cs_enabled' => 'enabled',
                    '_give_cs_base_amount' => $baseAmount,
                ]
            );

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }
    }

    /**
     * @param array $donation
     * @param array $params
     *
     * @return array
     */
    public function setDonationCurrency($donation, $params)
    {
        if (isset($params['donation_currency'])) {
            $donation['payment_currency'] = $params['donation_currency'];
        }

        return $donation;
    }

}
