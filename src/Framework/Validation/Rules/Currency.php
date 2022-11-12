<?php

declare(strict_types=1);

namespace Give\Framework\Validation\Rules;

use Closure;
use Give\Framework\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Framework\Validation\Contracts\ValidationRule;

class Currency implements ValidationRule, ValidatesOnFrontEnd
{
    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function id(): string
    {
        return 'currency';
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @unreleased
     *
     * @return string[]
     */
    public static function currencyCodes(): array
    {
        static $codes = null;

        if ($codes === null) {
            $codes = [
                "AFA",
                "ALL",
                "DZD",
                "AOA",
                "ARS",
                "AMD",
                "AWG",
                "AUD",
                "AZN",
                "BSD",
                "BHD",
                "BDT",
                "BBD",
                "BYR",
                "BEF",
                "BZD",
                "BMD",
                "BTN",
                "BTC",
                "BOB",
                "BAM",
                "BWP",
                "BRL",
                "GBP",
                "BND",
                "BGN",
                "BIF",
                "KHR",
                "CAD",
                "CVE",
                "KYD",
                "XOF",
                "XAF",
                "XPF",
                "CLP",
                "CNY",
                "COP",
                "KMF",
                "CDF",
                "CRC",
                "HRK",
                "CUC",
                "CZK",
                "DKK",
                "DJF",
                "DOP",
                "XCD",
                "EGP",
                "ERN",
                "EEK",
                "ETB",
                "EUR",
                "FKP",
                "FJD",
                "GMD",
                "GEL",
                "DEM",
                "GHS",
                "GIP",
                "GRD",
                "GTQ",
                "GNF",
                "GYD",
                "HTG",
                "HNL",
                "HKD",
                "HUF",
                "ISK",
                "INR",
                "IDR",
                "IRR",
                "IQD",
                "ILS",
                "ITL",
                "JMD",
                "JPY",
                "JOD",
                "KZT",
                "KES",
                "KWD",
                "KGS",
                "LAK",
                "LVL",
                "LBP",
                "LSL",
                "LRD",
                "LYD",
                "LTL",
                "MOP",
                "MKD",
                "MGA",
                "MWK",
                "MYR",
                "MVR",
                "MRO",
                "MUR",
                "MXN",
                "MDL",
                "MNT",
                "MAD",
                "MZM",
                "MMK",
                "NAD",
                "NPR",
                "ANG",
                "TWD",
                "NZD",
                "NIO",
                "NGN",
                "KPW",
                "NOK",
                "OMR",
                "PKR",
                "PAB",
                "PGK",
                "PYG",
                "PEN",
                "PHP",
                "PLN",
                "QAR",
                "RON",
                "RUB",
                "RWF",
                "SVC",
                "WST",
                "SAR",
                "RSD",
                "SCR",
                "SLL",
                "SGD",
                "SKK",
                "SBD",
                "SOS",
                "ZAR",
                "KRW",
                "XDR",
                "LKR",
                "SHP",
                "SDG",
                "SRD",
                "SZL",
                "SEK",
                "CHF",
                "SYP",
                "STD",
                "TJS",
                "TZS",
                "THB",
                "TOP",
                "TTD",
                "TND",
                "TRY",
                "TMT",
                "UGX",
                "UAH",
                "AED",
                "UYU",
                "USD",
                "UZS",
                "VUV",
                "VEF",
                "VND",
                "YER",
                "ZMK",
            ];
        }

        return $codes;
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function serializeOption()
    {
        return null;
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (!is_string($value) || !in_array(strtoupper($value), self::currencyCodes(), true)) {
            $fail(sprintf('%s must be a valid currency', '{field}'));
        }
    }
}
