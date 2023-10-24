import {useMemo} from 'react';
import amountFormatter from '@givewp/forms/app/utilities/amountFormatter';

/**
 * @since 3.0.0
 */
export default function useCurrencyFormatter(currency: Intl.NumberFormatOptions['currency'], options?: Intl.NumberFormatOptions) {
    return useMemo(() => amountFormatter(currency, options), [currency, navigator.language]);
}
