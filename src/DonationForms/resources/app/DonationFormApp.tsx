import {createRoot} from '@wordpress/element';
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
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {useEffect, useState} from 'react';
import type {Form as DonationForm} from '@givewp/forms/types';

const formTemplates = window.givewp.form.templates;
const GoalAchievedTemplate = withTemplateWrapper(formTemplates.layouts.goalAchieved);

/**
 * Get data from the server
 */
const {form, previewMode} = getWindowData();
const donationFormNodeSettings = getDonationFormNodeSettings(form);

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

/**
 * @since 3.0.0
 */
function App({form}: { form: DonationForm }) {
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
                    {!form.design?.includeHeaderInMultiStep && form.settings.showHeader && <Header form={form} />}
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

/**
 * @since 3.1.0
 */
function AppPreview() {
    const {
        subscribeToGoal,
        subscribeToColors,
        subscribeToSettings,
        subscribeToCss,
        unsubscribeAll
    } = useDonationFormPubSub();

    const [formState, setFormState] = useState<DonationForm>(form);

    useEffect(() => {
        subscribeToSettings((settings) => {
            setFormState((prevState) => {
                return {
                    ...prevState,
                    settings: {
                        ...prevState.settings,
                        ...settings,
                    },
                };
            });
        });

        subscribeToGoal((goal) => {
            setFormState((prevState) => {
                return {
                    ...prevState,
                    goal: {
                        ...prevState.goal,
                        ...goal,
                    },
                };
            });
        });

        subscribeToColors((data) => {
            if (data['primaryColor']) {
                root.style.setProperty('--givewp-primary-color', data['primaryColor']);
            }

            if (data['secondaryColor']) {
                root.style.setProperty('--givewp-secondary-color', data['secondaryColor']);
            }
        });

        subscribeToCss(({customCss}) => {
            let cssRules = '';
            const stylesheet = new CSSStyleSheet();

            stylesheet.replaceSync(customCss);

            for (let i = 0; i < stylesheet.cssRules.length; i++) {
                cssRules += stylesheet.cssRules[i].cssText + '\n';
            }

            style.innerText = cssRules;
        });

        return () => unsubscribeAll();
    }, []);

    return <App form={formState} />;
}

const root = document.getElementById('root-givewp-donation-form');
const style = document.getElementById('root-givewp-donation-form-style');

createRoot(root).render(previewMode ? <AppPreview /> : <App form={form} />);
