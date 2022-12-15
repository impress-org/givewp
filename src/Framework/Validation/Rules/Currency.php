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
                "ALL",
                "AFN",
                "ARS",
                "AWG",
                "AUD",
                "AZN",
                "BSD",
                "BBD",
                "BDT",
                "BYR",
                "BZD",
                "BMD",
                "BOB",
                "BAM",
                "BWP",
                "BGN",
                "BRL",
                "BND",
                "KHR",
                "CAD",
                "KYD",
                "CLP",
                "CNY",
                "COP",
                "CRC",
                "HRK",
                "CUP",
                "CZK",
                "DKK",
                "DOP",
                "XCD",
                "EGP",
                "SVC",
                "EEK",
                "EUR",
                "FKP",
                "FJD",
                "GHC",
                "GIP",
                "GTQ",
                "GGP",
                "GYD",
                "HNL",
                "HKD",
                "HUF",
                "ISK",
                "INR",
                "IDR",
                "IRR",
                "IMP",
                "ILS",
                "JMD",
                "JPY",
                "JEP",
                "KZT",
                "KPW",
                "KRW",
                "KGS",
                "LAK",
                "LVL",
                "LBP",
                "LRD",
                "LTL",
                "MKD",
                "MYR",
                "MUR",
                "MXN",
                "MNT",
                "MZN",
                "NAD",
                "NPR",
                "ANG",
                "NZD",
                "NIO",
                "NGN",
                "NOK",
                "OMR",
                "PKR",
                "PAB",
                "PYG",
                "PEN",
                "PHP",
                "PLN",
                "QAR",
                "RON",
                "RUB",
                "SHP",
                "SAR",
                "RSD",
                "SCR",
                "SGD",
                "SBD",
                "SOS",
                "ZAR",
                "LKR",
                "SEK",
                "CHF",
                "SRD",
                "SYP",
                "TWD",
                "THB",
                "TTD",
                "TRY",
                "TRL",
                "TVD",
                "UAH",
                "GBP",
                "USD",
                "UYU",
                "UZS",
                "VEF",
                "VND",
                "YER",
                "ZWD",
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
            $fail(sprintf(__('%s must be a valid currency', 'give'), '{field}'));
        }
    }
}
