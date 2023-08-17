import {createContext, ReactNode, useContext, useReducer} from 'react';
import reducer from './reducer';
import {StepObject} from '@givewp/forms/app/form/MultiStepForm/types';

const StoreContext = createContext(null);
StoreContext.displayName = 'DonationFormMultiStepState';

const StoreContextDispatch = createContext(null);
StoreContextDispatch.displayName = 'DonationFormMultiStepStateDispatch';

export type MultiStepFormContextState = {
    steps: StepObject[];
    currentStep: number;
};

type PropTypes = {
    initialState: MultiStepFormContextState;
    children: ReactNode;
};

/**
 * @since 3.0.0
 */
const DonationFormMultiStepStateProvider = ({initialState, children}: PropTypes) => {
    const [state, dispatch] = useReducer(reducer, initialState);

    return (
        <StoreContext.Provider value={state}>
            <StoreContextDispatch.Provider value={dispatch}>{children}</StoreContextDispatch.Provider>
        </StoreContext.Provider>
    );
};

const useDonationFormMultiStepState = () => useContext<MultiStepFormContextState>(StoreContext);
const useDonationFormMultiStepStateDispatch = () => useContext(StoreContextDispatch);

export {DonationFormMultiStepStateProvider, useDonationFormMultiStepState, useDonationFormMultiStepStateDispatch};
