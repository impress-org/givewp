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
            <PanelBody title={__('Email Settings', 'givewp')} initialOpen={false}>
                <PanelRow>
                    <SelectControl
                        label={__('Email Options', 'givewp')}
                        options={[
                            {label: __('Global', 'givewp'), value: 'global'},
                            {label: __('Customize', 'givewp'), value: 'enabled'},
                        ]}
                        value={emailOptionsStatus}
                        onChange={(emailOptionsStatus) => dispatch(setFormSettings({emailOptionsStatus}))}
                    />
                </PanelRow>
                {'enabled' === emailOptionsStatus && (
                    <>
                        <PanelRow>
                            <SelectControl
                                label={__('Email Template', 'givewp')}
                                help={__('Choose your template from the available registered template types', 'givewp')}
                                options={[
                                    {label: __('Default template', 'givewp'), value: 'default'},
                                    {label: __('No template, plain text only', 'givewp'), value: 'none'},
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
                                label={__('From Name', 'givewp')}
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
                                label={__('From Email', 'givewp')}
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
