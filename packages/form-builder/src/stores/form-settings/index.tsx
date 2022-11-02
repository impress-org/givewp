import {createContext, ReactNode, useContext, useReducer} from 'react';
import formSettingsReducer, {setFormBlocks, setFormSettings} from './reducer.ts';
import type {Block} from '../../types/block';

const StoreContext = createContext(null);
StoreContext.displayName = 'FormSettingsStoreContext';

const StoreContextDispatch = createContext(null);
StoreContextDispatch.displayName = 'FormSettingsStoreContextDispatch';

/**
 * @unreleased
 */
export type FormSettings = {
    blocks: Block[];
    formTitle: string;
    enableDonationGoal: boolean;
    enableAutoClose: boolean;
    registration: string;
    goalFormat: string;
};

/**
 * This is our Form's store for its settings.
 * It uses the React hook useReducer to store and persist state.
 * In order to update the state a "dispatch" method will be provided from the reducer.
 * This provides a lot of flexibility and performance benefits.
 *
 * @see https://reactjs.org/docs/hooks-reference.html#usereducer
 *
 * @unreleased
 */
const FormSettingsProvider = ({initialState, children}: {initialState: FormSettings; children: ReactNode}) => {
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
 * @unreleased
 *
 * @example
 *  const {formTitle} = useFormSettings();
 */
const useFormSettings = () => useContext<FormSettings>(StoreContext);

/**
 * This is a convenient way of retrieving the "dispatch" function from our provider
 *
 * @unreleased
 *
 * @example
 * // retrieve the "dispatch" function
 * const dispatch = useCampaignSettingsDispatch();
 *
 * // use the "dispatch" function
 * dispatch(setFormSettings({formTitle: 'new title'}));
 */
const useFormSettingsDispatch = () => useContext(StoreContextDispatch);

export {FormSettingsProvider, useFormSettings, useFormSettingsDispatch, setFormSettings, setFormBlocks};
