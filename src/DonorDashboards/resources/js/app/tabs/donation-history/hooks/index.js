import { useEffect, useState } from '@wordpress/element';
import {store} from '../store';

export const useSelector = (select) => {
    const [selected, setSelected] = useState(select(store.getState()));
    useEffect(() => {
        const handleChange = () => {
            setSelected(select(store.getState()));
        };
        const unsubscribe = store.subscribe(handleChange);
        return function cleanup() {
            unsubscribe();
        };
    }, []);
    return selected;
};
