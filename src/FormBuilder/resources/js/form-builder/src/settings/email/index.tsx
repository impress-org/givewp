import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, SelectControl, TextControl} from '@wordpress/components';
import EmailTemplateOptions from './template-options';
import LogoUpload from './logo-upload';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

export default () => {
    const {
        settings: {emailOptionsStatus, emailTemplate, emailLogo, emailFromName, emailFromEmail},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <>
            <PanelBody title={__('Email Settings', 'give')} initialOpen={false}>
                <PanelRow>
                    <SelectControl
                        label={__('Email Options', 'give')}
                        options={[
                            {label: __('Global', 'give'), value: 'global'},
                            {label: __('Customize', 'give'), value: 'enabled'},
                        ]}
                        value={emailOptionsStatus}
                        onChange={(emailOptionsStatus) => dispatch(setFormSettings({emailOptionsStatus}))}
                    />
                </PanelRow>
                {'enabled' === emailOptionsStatus && (
                    <>
                        <PanelRow>
                            <SelectControl
                                label={__('Email Template', 'give')}
                                help={__('Choose your template from the available registered template types', 'give')}
                                options={[
                                    {label: __('Default template', 'give'), value: 'default'},
                                    {label: __('No template, plain text only', 'give'), value: 'none'},
                                ]}
                                value={emailTemplate}
                                onChange={(emailTemplate) => dispatch(setFormSettings({emailTemplate}))}
                            />
                        </PanelRow>
                        <PanelRow>
                            <LogoUpload
                                value={emailLogo}
                                onChange={(emailLogo) => dispatch(setFormSettings({emailLogo}))}
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label={__('From Name', 'give')}
                                help={__(
                                    'The name which appears in the "From" field in all GiveWP donation emails.',
                                    'givewp'
                                )}
                                value={emailFromName}
                                onChange={(emailFromName) => dispatch(setFormSettings({emailFromName}))}
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label={__('From Email', 'give')}
                                help={__(
                                    'Email address from which all GiveWP emails are sent from. This will act as the "from" and "reply-to" email address.',
                                    'givewp'
                                )}
                                value={emailFromEmail}
                                onChange={(emailFromEmail) => dispatch(setFormSettings({emailFromEmail}))}
                            />
                        </PanelRow>
                        <PanelRow>
                            <EmailTemplateOptions />
                        </PanelRow>
                    </>
                )}
            </PanelBody>
        </>
    );
};
