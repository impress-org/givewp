import {useMemo} from 'react';
import amountFormatter from '@givewp/forms/app/utilities/amountFormatter';

/**
 * @since 0.1.0
 */
export default function useCurrencyFormatter<AmountFormatter>(currency, options) {
    return useMemo(() => amountFormatter(currency, options), [currency, navigator.language]);
}
