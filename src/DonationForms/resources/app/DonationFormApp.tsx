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
            if (settings['designSettingsSectionStyle']) {
                updateDesignSettingsClassName(
                    'givewp-design-settings--section-style',
                    settings['designSettingsSectionStyle']
                );
            }

            if (settings['designSettingsImageUrl']) {
                root.style.setProperty(
                    '--givewp-design-settings-background-image',
                    'url(' + settings['designSettingsImageUrl'] + ')'
                );

                const style = settings['designSettingsImageStyle'] ? settings['designSettingsImageStyle'] : 'background';

                updateDesignSettingsClassName('givewp-design-settings--image-style', style);
            }

            if (settings['designSettingsLogoUrl']) {
                root.style.setProperty(
                    '--givewp-design-settings-logo',
                    'url(' + settings['designSettingsLogoUrl'] + ')'
                );
                root.classList.add('givewp-design-settings--logo');

                const position = settings['designSettingsLogoPosition'] ? settings['designSettingsLogoPosition'] : 'left';
                updateDesignSettingsClassName('givewp-design-settings--logo-position', position);
            }

            if (settings['designSettingsTextFieldStyle']) {
                updateDesignSettingsClassName(
                    'givewp-design-settings--textField-style',
                    settings['designSettingsTextFieldStyle']
                );
            }

            if (!settings['designSettingsImageUrl']) {
                // reset/remove classnames on delete
                root.style.setProperty('--givewp-design-settings-background-image', '');
                updateDesignSettingsClassName('givewp-design-settings--image-style', '');

                // reconstruct branding container & logo container
                root.classList.add('givewp-design-settings--logo');
                root.style.setProperty(
                    '--givewp-design-settings-logo',
                    'url(' + settings['designSettingsLogoUrl'] + ')'
                );
                updateDesignSettingsClassName('givewp-design-settings--logo-position', settings['designSettingsLogoPosition']);
            }

            // reset/remove classnames on delete
            if (!settings['designSettingsLogoUrl']) {
                root.style.setProperty('--givewp-design-settings-logo', '');
                root.classList.remove('givewp-design-settings--logo');
                updateDesignSettingsClassName('givewp-design-settings--logo-position', '');
            }

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

function updateDesignSettingsClassName(block, element) {
    root.classList.forEach((className) => {
        if (className.startsWith(block + '__')) {
            root.classList.remove(className);
        }
    });
    root.classList.add(block + '__' + element);
}

const root = document.getElementById('root-givewp-donation-form');
const style = document.getElementById('root-givewp-donation-form-style');

if (createRoot) {
    createRoot(root).render(previewMode ? <AppPreview /> : <App form={form} />);
} else {
    render(previewMode ? <AppPreview /> : <App form={form} />, root);
}
