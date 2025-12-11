/*
 * @since 3.20.0
 */
export default function parseValueFromLocale(amount: string): string {
    if (!amount) {
        return amount;
    }

    const numberFormat = new Intl.NumberFormat(window.navigator.language);
    const parts = numberFormat.formatToParts(1234.56);

    let groupSeparator: string;
    let decimalSeparator: string;

    for (const part of parts) {
        if (part.type === 'group') {
            groupSeparator = part.value;
        } else if (part.type === 'decimal') {
            decimalSeparator = part.value;
        }
    }

    return amount.replaceAll(groupSeparator, '').replace(decimalSeparator, '.');
}
