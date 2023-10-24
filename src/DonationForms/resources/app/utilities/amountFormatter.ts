export interface AmountFormatter  {
    currency: string,
    options?: Intl.NumberFormatOptions
}
/**
 * @since 3.0.0
 */
export default function amountFormatter(currency: Intl.NumberFormatOptions['currency'], options?: Intl.NumberFormatOptions): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options
    });
}
