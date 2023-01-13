import {useEffect, useRef} from 'react';
import {debounce} from 'lodash';

export default function useDebounce(callback) {
    const debouncedCallback = useRef(debounce(callback, 500)).current;

    useEffect(() => {
        return () => {
            debouncedCallback.cancel();
        };
    }, []);

    return debouncedCallback;
}
