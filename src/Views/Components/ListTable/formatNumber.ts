/**
 * The separators of the signed-in user's locale. PHP prints them before the shared list table script, from
 * the same place `number_format_i18n()` reads, so a number in a cell and a number in the footer match.
 * This is configuration, not records.
 *
 * @since TBD
 */
export interface GiveListTableNumbers {
    thousandsSep: string;
    decimalPoint: string;
}

declare global {
    interface Window {
        GiveListTableNumbers?: GiveListTableNumbers;
    }
}

const DEFAULT_THOUSANDS_SEPARATOR = ',';

/**
 * Writes a whole number with the thousands separator of the admin's locale: 1200 is "1,200" in en_US and
 * "1.200" in de_DE. The separator can be any text, also more than one character. Counts are whole numbers,
 * so a fraction is dropped.
 *
 * @since TBD
 */
export function formatNumber(value: number): string {
    const separator = window.GiveListTableNumbers?.thousandsSep ?? DEFAULT_THOUSANDS_SEPARATOR;
    const whole = Math.trunc(value);

    if (!Number.isFinite(whole)) {
        return String(value);
    }

    const digits = String(Math.abs(whole)).replace(/\B(?=(\d{3})+(?!\d))/g, () => separator);

    return whole < 0 ? `-${digits}` : digits;
}

/**
 * Puts formatted numbers into an already translated message, in place of their `%d` placeholders. The
 * message keeps its original text (`%1$d of %2$d Items`) when it is translated, so the translations that
 * already exist keep matching. The numbers go in after that, so they cannot be turned into text by `sprintf`.
 *
 * @since TBD
 *
 * @param translated The message after `__()`, `_n()` or `_x()`.
 * @param numbers The placeholder as written in the message (for example `%1$d`), and its number.
 */
export function insertNumbers(translated: string, numbers: Record<string, number>): string {
    return Object.entries(numbers).reduce(
        (message, [placeholder, value]) => message.split(placeholder).join(formatNumber(value)),
        translated
    );
}
