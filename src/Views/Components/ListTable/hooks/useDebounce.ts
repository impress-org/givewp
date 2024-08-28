import {useEffect, useRef} from 'react';
//import {debounce} from 'lodash';
import debounce from 'lodash.debounce';

export default function useDebounce(callback) {
    console.log('debounce: ', debounce);
    const debouncedCallback = useRef(debounce(callback, 500)).current;

    useEffect(() => {
        return () => {
            debouncedCallback.cancel();
        };
    }, []);

    return debouncedCallback;
}
