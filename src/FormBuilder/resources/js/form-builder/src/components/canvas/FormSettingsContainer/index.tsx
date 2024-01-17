import {createContext, Dispatch, useContext, useReducer} from 'react';
import {
    Action,
    formSettingsReducer,
    State,
} from '@givewp/form-builder/components/canvas/FormSettingsContainer/formSettingsReducer';
import Menu from './components/Menu';
import Content from './components/Content';

const FormSettingsContext = createContext<[State, Dispatch<Action>] | undefined>(undefined);

/**
 * @since 3.3.0
 */
export function useFormSettingsContext() {
    const context = useContext(FormSettingsContext);

    if (!context) {
        throw new Error('useFormSettingsContext must be used within a FormSettingsContextProvider');
    }

    return context;
}

/**
 * @since 3.3.0
 */
export default function FormSettingsContainer({routes}) {
    const [state, dispatch] = useReducer(formSettingsReducer, {
        menuPage: 1,
        activeMenu: 'general',
        activeRoute: 'general',
    });

    return (
        <FormSettingsContext.Provider value={[state, dispatch]}>
            <div className={'givewp-form-settings'}>
                <Menu routes={routes} />
                <Content routes={routes} />
            </div>
        </FormSettingsContext.Provider>
    );
}
