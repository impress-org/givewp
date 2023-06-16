import {useEffect, useRef} from '@wordpress/element';

/**
 * @since 0.4.0
 */
export default function usePrevious<T>(value: T): T {
    const ref: any = useRef<T>();

    useEffect(() => {
        ref.current = value;
    }, [value]);

    return ref.current;
}
