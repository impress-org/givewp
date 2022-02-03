import {useState, useEffect} from 'react';

// hook to debounce a variable. see https://usehooks.com/useDebounce/

export default function useDebounce(value: any, delay: number) {
    const [debouncedValue, setDebouncedValue] = useState(value);
    useEffect(
        () => {
            const handler = setTimeout(() => {
                setDebouncedValue(value);
            }, delay);
            return () => {
                clearTimeout(handler);
            };
        },
        [value, delay]
    );
    return debouncedValue;
}
