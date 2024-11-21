import { useState, useEffect } from 'react';

/**
 * Custom hook to determine the group and decimal separators based on locale.
 */
export default function useCurrencySeparator(){
    const [groupSeparator, setGroupSeparator] = useState(',');
    const [decimalSeparator, setDecimalSeparator] = useState('.');

    useEffect(() => {
        const formatter = new Intl.NumberFormat();
        const getGroupSeparator = formatter.format(1000).replace(/[0-9]/g, '');
        const getDecimalSeparator = formatter.format(1.1).replace(/[0-9]/g, '');

        // Ensure separators are not the same.
        if (getGroupSeparator === getDecimalSeparator) {
            setDecimalSeparator(getDecimalSeparator === '.' ? ',' : '.');
        } else {
            setGroupSeparator(getGroupSeparator);
            setDecimalSeparator(getDecimalSeparator);
        }
    }, []);

    return { groupSeparator, decimalSeparator };
};

