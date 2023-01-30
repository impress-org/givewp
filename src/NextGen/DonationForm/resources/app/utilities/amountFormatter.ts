export interface AmountFormatter  {
    currency: string,
    options?: Intl.NumberFormatOptions
}
/**
 * @since 0.1.0
 */
export default function amountFormatter<AmountFormatter>(currency, options): Intl.NumberFormat
{
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options
    });
}
