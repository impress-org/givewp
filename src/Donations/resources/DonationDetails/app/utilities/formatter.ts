import {currencyFormat} from "../../../window";

/**
 * @unreleased
 */
export interface AmountFormatter {
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
}

/**
 * @unreleased
 */

export function parseAmountValue(num: number): number {
    return num ? formatDecimalPlacement(num) : 0;
}

/**
 * @unreleased
 */

export function formatDecimalPlacement(num: number) {
    return currencyFormat.number_decimals > 0 ? num / 100 : num;
}
