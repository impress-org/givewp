import {createContext, ReactNode, useContext, useReducer, MutableRefObject} from 'react';
import type {Gateway} from '@givewp/forms/types';
import {ObjectSchema} from 'joi';
import reducer from '@givewp/forms/app/store/reducer';

const StoreContext = createContext(null);
StoreContext.displayName = 'DonationFormState';

const StoreContextDispatch = createContext(null);
StoreContextDispatch.displayName = 'DonationFormStateDispatch';

/**
 * @unreleased Adds a refs property to the state to store references to form elements.
 */
interface DonationFormState {
    gateways: Gateway[];
    defaultValues: object;
    validationSchema: ObjectSchema;
    refs?: Record<string, MutableRefObject<HTMLElement | null> | null>;
}

type PropTypes = {
    initialState: DonationFormState;
    children: ReactNode;
};

/**
 * @since 3.0.0
 */
const DonationFormStateProvider = ({initialState, children}: PropTypes) => {
    const [state, dispatch] = useReducer(reducer, initialState);

    return (
        <StoreContext.Provider value={state}>
            <StoreContextDispatch.Provider value={dispatch}>{children}</StoreContextDispatch.Provider>
        </StoreContext.Provider>
    );
};

const useDonationFormState = () => useContext<DonationFormState>(StoreContext);
const useDonationFormStateDispatch = () => useContext(StoreContextDispatch);

export {DonationFormStateProvider, useDonationFormState, useDonationFormStateDispatch};
