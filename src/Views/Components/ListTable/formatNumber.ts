/**
 * The language of the browser, as in "pt-BR". The list requests send it, so the server formats money and
 * counts the same way as the numbers written here.
 *
 * @since TBD
 */
export function browserLocale(): string {
    return window.navigator.language || window.navigator.languages?.[0] || '';
}

// `useGrouping` also groups four-digit numbers in the languages that would not by default, as PHP does.
const options: Intl.NumberFormatOptions = {maximumFractionDigits: 0, useGrouping: true};

function numberFormat(): Intl.NumberFormat {
    try {
        return new Intl.NumberFormat(browserLocale() || undefined, options);
    } catch {
        return new Intl.NumberFormat(undefined, options);
    }
}

/**
 * Writes a whole number in the browser's language: 1200 is "1,200" in en-US and "1.200" in de-DE. Counts are
 * whole numbers, so a fraction is dropped.
 *
 * @since TBD
 */
export function formatNumber(value: number): string {
    if (!Number.isFinite(value)) {
        return String(value);
    }

    return numberFormat().format(Math.trunc(value));
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
