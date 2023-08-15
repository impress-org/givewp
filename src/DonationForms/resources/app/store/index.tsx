import {createContext, ReactNode, useContext, useReducer} from 'react';
import type {Gateway} from '@givewp/forms/types';
import reducer from '@givewp/forms/app/store/reducer';
import {ObjectSchema} from 'joi';

const StoreContext = createContext(null);
StoreContext.displayName = 'DonationFormState';

const StoreContextDispatch = createContext(null);
StoreContextDispatch.displayName = 'DonationFormStateDispatch';

type PropTypes = {
    initialState: {
        gateways: Gateway[];
        defaultValues: object;
        validationSchema: ObjectSchema;
    };
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

const useDonationFormState = () => useContext<PropTypes['initialState']>(StoreContext);
const useDonationFormStateDispatch = () => useContext(StoreContextDispatch);

export {DonationFormStateProvider, useDonationFormState, useDonationFormStateDispatch};
