export interface AmountFormatter  {
    currency: string,
    options?: Intl.NumberFormatOptions
}
/**
 * @unreleased
 */
export default function amountFormatter<AmountFormatter>(currency, options): Intl.NumberFormat
{
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options
    });
}
