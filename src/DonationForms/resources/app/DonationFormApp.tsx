import {createRoot, render} from '@wordpress/element';
import getDefaultValuesFromSections from './utilities/getDefaultValuesFromSections';
import Form from './form/Form';
import {DonationFormStateProvider} from './store';
import getWindowData from './utilities/getWindowData';
import prepareFormData from './utilities/PrepareFormData';
import getJoiRulesForForm from './utilities/ConvertFieldAPIRulesToJoi';
import Header from './form/Header';
import mountWindowData from '@givewp/forms/app/utilities/mountWindowData';
import {withTemplateWrapper} from '@givewp/forms/app/templates';
import DonationFormErrorBoundary from '@givewp/forms/app/errors/boundaries/DonationFormErrorBoundary';
import MultiStepForm from '@givewp/forms/app/form/MultiStepForm';
import getDonationFormNodeSettings from '@givewp/forms/app/utilities/getDonationFormNodeSettings';
import {DonationFormSettingsProvider} from '@givewp/forms/app/store/form-settings';
import usePubSub from '@givewp/forms/app/utilities/usePubSub';
import {useState, useEffect} from 'react';
import {FormSettings} from '@givewp/form-builder/types';

const formTemplates = window.givewp.form.templates;
const GoalAchievedTemplate = withTemplateWrapper(formTemplates.layouts.goalAchieved);

/**
 * Get data from the server
 */
const {form: initialFormState} = getWindowData();
const donationFormNodeSettings = getDonationFormNodeSettings(initialFormState);

prepareFormData(initialFormState);

mountWindowData();

/**
 * Prepare default values for form
 */
const defaultValues = getDefaultValuesFromSections(initialFormState.nodes);

const schema = getJoiRulesForForm(initialFormState);

const initialState = {
    defaultValues,
    gateways: window.givewp.gateways.getAll(),
    validationSchema: schema,
};

/**
 * @since 3.0.0
 */
function App({preview}) {

    const {subscribe} = usePubSub();
    const [form, setFormState] = useState(initialFormState);

    useEffect(() => {
        if (preview) {
            subscribe('preview:settings', (data: FormSettings) => {
                setFormState(prevState => {
                    return {
                        ...prevState,
                        settings: {
                            ...prevState.settings,
                            ...data
                        }
                    }
                })
            })

            subscribe('preview:goal', (data: { [s: string]: string }) => {
                setFormState(prevState => {
                    return {
                        ...prevState,
                        goal: {
                            ...prevState.goal,
                            ...data
                        }
                    }
                })
            })

            subscribe('preview:colors', (data: { [s: string]: string }) => {
                const [key, value] = Object.entries<string>(data).flat();

                switch (key) {
                    case 'primaryColor':
                        root.style.setProperty('--givewp-primary-color', value);
                        break;
                    case 'secondaryColor':
                        root.style.setProperty('--givewp-secondary-color', value);
                        break;
                }
            })
        }
    }, []);

    if (form.goal.isAchieved) {
        return (
            <DonationFormErrorBoundary>
                <GoalAchievedTemplate goalAchievedMessage={form.settings.goalAchievedMessage} />
            </DonationFormErrorBoundary>
        );
    }

    if (form.design?.isMultiStep) {
        return (
            <DonationFormSettingsProvider value={{...form.settings, ...donationFormNodeSettings}}>
                <DonationFormStateProvider initialState={initialState}>
                    <MultiStepForm form={form} />
                </DonationFormStateProvider>
            </DonationFormSettingsProvider>
        );
    }

    return (
        <DonationFormSettingsProvider value={{...form.settings, ...donationFormNodeSettings}}>
            <DonationFormStateProvider initialState={initialState}>
                {form.settings?.showHeader && <Header form={form} />}
                <Form defaultValues={defaultValues} sections={form.nodes} validationSchema={schema} />
            </DonationFormStateProvider>
        </DonationFormSettingsProvider>
    );
}

const root = document.getElementById('root-givewp-donation-form');
const preview = root.classList.contains('givewp-donation-form--preview');

if (createRoot) {
    createRoot(root).render(<App preview={preview} />);
} else {
    render(<App preview={preview} />, root);
}
