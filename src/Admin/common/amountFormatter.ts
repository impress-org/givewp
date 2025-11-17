/**
 * @since 4.0.0
 */
export function amountFormatter(
    currency: Intl.NumberFormatOptions['currency'],
    options?: Intl.NumberFormatOptions
): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options,
    });
}
