import {useRef} from '@wordpress/element';
import {useEffect} from 'react';

/**
 * @unreleased
 */
export default function usePrevious<T>(value: T): T {
    const ref: any = useRef<T>();

    useEffect(() => {
        ref.current = value;
    }, [value]);

    return ref.current;
}