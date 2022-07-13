import {useState} from 'react';

const useToggleState = (initialState = false) => {
    const [state, update] = useState(initialState);

    const toggle = () => (
        update(prev => !prev)
    );

    return {state, update, toggle};
};

export default useToggleState;
