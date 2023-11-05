import {createContext, Dispatch, useContext, useReducer} from 'react';
import {
    Action,
    formSettingsReducer,
    State,
} from '@givewp/form-builder/components/canvas/FormSettingsContainer/formSettingsReducer';

const FormSettingsContext = createContext<[State, Dispatch<Action>] | undefined>(undefined);

export function useFormSettingsContext() {
    const context = useContext(FormSettingsContext);

    if (!context) {
        throw new Error('useFormSettingsContext must be used within a FormSettingsContextProvider');
    }

    return context;
}

export default function FormSettingsContainer({children}) {
   const [state, dispatch] = useReducer(formSettingsReducer, {
       content: null,
       menuPage: 1,
       activeMenu: 'item-general',
       menuStack: [],
   });

    return (
        <FormSettingsContext.Provider value={[state, dispatch]}>
            <div className={'givewp-form-settings'}>
                <div className={'givewp-form-settings__menu'}>
                    <ul className={`givewp-form-settings__menu__page-${state.menuPage}`}>
                        {children}
                        {wp.hooks.applyFilters('givewp_form_builder_pdf_settings', '')}
                    </ul>
                </div>
                <div className={'givewp-form-settings__content'}>{state.content}</div>
            </div>
        </FormSettingsContext.Provider>
    );
}
