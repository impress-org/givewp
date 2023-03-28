/**
 * @unreleased
 */
export interface AmountFormatter  {
    currency: string,
    options?: Intl.NumberFormatOptions
}

/**
 * @unreleased
 */
export function amountFormatter(currency: Intl.NumberFormatOptions['currency'], options?: Intl.NumberFormatOptions): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options
    });
}

/**
 * @unreleased
 */
export function formatCurrency(amount: number, currency: string) {
    return amountFormatter(currency, {
        maximumFractionDigits: 2,
    }).format(amount);
};


export function parseAmountValue(num: number): number {
    return num ? num / 100 : 0;
}
