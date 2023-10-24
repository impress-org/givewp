import {createContext, ReactNode, useContext, useReducer} from 'react';
import formSettingsReducer, {setFormBlocks, setFormSettings, setTransferState} from './reducer';
import {FormState} from '@givewp/form-builder/types';

const StoreContext = createContext(null);
StoreContext.displayName = 'FormState';

const StoreContextDispatch = createContext(null);
StoreContextDispatch.displayName = 'FormStateDispatch';

/**
 * This is our Form's store for its settings.
 * It uses the React hook useReducer to store and persist state.
 * In order to update the state a "dispatch" method will be provided from the reducer.
 * This provides a lot of flexibility and performance benefits.
 *
 * @see https://reactjs.org/docs/hooks-reference.html#usereducer
 *
 * @since 3.0.0
 */
const FormStateProvider = ({initialState, children}: { initialState: FormState; children: ReactNode }) => {
    const [state, dispatch] = useReducer(formSettingsReducer, initialState);

    return (
        <StoreContext.Provider value={state}>
            <StoreContextDispatch.Provider value={dispatch}>{children}</StoreContextDispatch.Provider>
        </StoreContext.Provider>
    );
};

/**
 * This is a convenient way of retrieving all the settings from the store as readOnly
 *
 * @since 3.0.0
 *
 * @example
 *  const {settings: {formTitle}} = useFormState();
 */
const useFormState = () => useContext<FormState>(StoreContext);

/**
 * This is a convenient way of retrieving the "dispatch" function from our provider
 *
 * @since 3.0.0
 *
 * @example
 * // retrieve the "dispatch" function
 * const dispatch = useFormStateDispatch();
 *
 * // use the "dispatch" function
 * dispatch(setFormSettings({formTitle: 'new title'}));
 */
const useFormStateDispatch = () => useContext(StoreContextDispatch);

export {FormStateProvider, useFormState, useFormStateDispatch, setFormSettings, setFormBlocks, setTransferState};
