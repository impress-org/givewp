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
import useIframeMessages from '@givewp/forms/app/utilities/IframeMessages';
import {useState} from 'react';

const formTemplates = window.givewp.form.templates;
const GoalAchievedTemplate = withTemplateWrapper(formTemplates.layouts.goalAchieved);

/**
 * Get data from the server
 */
const {form} = getWindowData();

prepareFormData(form);

mountWindowData();

/**
 * Prepare default values for form
 */
const defaultValues = getDefaultValuesFromSections(form.nodes);

const schema = getJoiRulesForForm(form);

const initialState = {
    defaultValues,
    gateways: window.givewp.gateways.getAll(),
    validationSchema: schema,
};

const formInitialSettings = {...form.settings, ...getDonationFormNodeSettings(form)};

/**
 * @since 3.0.0
 */
function App({preview}) {

    const {subscribe} = useIframeMessages();
    const [formSettings, setFormSettings] = useState(formInitialSettings);


    subscribe('designPreview', data => {
        setFormSettings(prevState => {
            return {
                ...prevState,
                ...data
            }
        })
    })

    if (form.goal.isAchieved) {
        return (
            <DonationFormErrorBoundary>
                <GoalAchievedTemplate goalAchievedMessage={form.settings.goalAchievedMessage} />
            </DonationFormErrorBoundary>
        );
    }

    if (form.design?.isMultiStep) {
        return (
            <DonationFormSettingsProvider value={formSettings}>
                <DonationFormStateProvider initialState={initialState}>
                    <MultiStepForm sections={form.nodes} showHeader={form.settings?.showHeader} />
                </DonationFormStateProvider>
            </DonationFormSettingsProvider>
        );
    }

    return (
        <DonationFormSettingsProvider value={formSettings}>
            <DonationFormStateProvider initialState={initialState}>
                {form.settings?.showHeader && <Header />}
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
