import {CheckboxControl, PanelBody, PanelRow, SelectControl, TextControl} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {InspectorControls} from '@wordpress/block-editor';
import {Markup} from 'interweave';
import {ClassicEditor} from '@givewp/form-builder-library';
import GlobalSettingsLink from '@givewp/form-builder/blocks/fields/terms-and-conditions/GlobalSettingsLink';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import ControlForPopover from '@givewp/form-builder/components/settings/ControlForPopover';
import StyledPopover from '@givewp/form-builder/blocks/fields/terms-and-conditions/StyledPopover';

import './styles.scss';

const DisplayTypeEnum = {
    SHOW_MODAL_TERMS: 'showModalTerms',
    SHOW_FORM_TERMS: 'showFormTerms',
    SHOW_LINK_TERMS: 'showLinkTerms',
};

export default function Edit({
    attributes: {
        checkboxLabel,
        displayType,
        linkText,
        useGlobalSettings,
        linkUrl,
        agreementText,
        modalHeading,
        modalAcceptanceText,
    },
    setAttributes,
}: BlockEditProps<any>) {
    const [showAgreementTextModal, setShowAgreementTextModal] = useState(false);
    const globalSettings = getFormBuilderWindowData().termsAndConditions;

    if (useGlobalSettings) {
        checkboxLabel = globalSettings.checkboxLabel;
        agreementText = globalSettings.agreementText;
    }

    const isModalDisplay = displayType === DisplayTypeEnum.SHOW_MODAL_TERMS;
    const isFormDisplay = displayType === DisplayTypeEnum.SHOW_FORM_TERMS;
    const isLinkDisplay = displayType === DisplayTypeEnum.SHOW_LINK_TERMS;

    return (
        <>
            <CheckboxPlaceholder
                label={checkboxLabel}
                linkText={linkText}
                isFormDisplay={isFormDisplay}
                agreementText={agreementText}
            />

            <InspectorControls>
                <PanelBody title={__('Field Options', 'give')} initialOpen={true}>
                    <PanelRow>
                        <SelectControl
                            label={__('TERMS AND CONDITIONS', 'give')}
                            onChange={() => setAttributes({useGlobalSettings: !useGlobalSettings})}
                            value={useGlobalSettings}
                            options={[
                                {label: __('Global', 'give'), value: 'true'},
                                {label: __('Customize', 'give'), value: 'false'},
                            ]}
                        />
                    </PanelRow>

                    {useGlobalSettings ? (
                        <GlobalSettingsLink
                            href={
                                '/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=display&section=term-and-conditions'
                            }
                        />
                    ) : (
                        <>
                            <PanelRow>
                                <TextControl
                                    label={__('Checkbox Label', 'give')}
                                    value={checkboxLabel}
                                    onChange={(value) => setAttributes({checkboxLabel: value})}
                                />
                            </PanelRow>
                            <PanelRow>
                                <SelectControl
                                    label={__('Display Type', 'give')}
                                    onChange={(value) => setAttributes({displayType: value})}
                                    value={displayType}
                                    options={[
                                        {
                                            label: __('Show terms in modal', 'give'),
                                            value: DisplayTypeEnum.SHOW_MODAL_TERMS,
                                        },
                                        {
                                            label: __('Show terms in form', 'give'),
                                            value: DisplayTypeEnum.SHOW_FORM_TERMS,
                                        },
                                        {
                                            label: __('Link to terms', 'give'),
                                            value: DisplayTypeEnum.SHOW_LINK_TERMS,
                                        },
                                    ]}
                                />
                            </PanelRow>

                            {(isLinkDisplay || isModalDisplay) && (
                                <PanelRow>
                                    <TextControl
                                        label={__('Link Text', 'give')}
                                        value={linkText}
                                        onChange={(value) => setAttributes({linkText: value})}
                                    />
                                </PanelRow>
                            )}

                            {isLinkDisplay && (
                                <PanelRow>
                                    <TextControl
                                        label={__('URL', 'give')}
                                        value={linkUrl}
                                        onChange={(value) => setAttributes({linkUrl: value})}
                                    />
                                </PanelRow>
                            )}

                            {!isLinkDisplay && (
                                <PanelRow>
                                    <ControlForPopover
                                        id="terms-and-conditions"
                                        help={__(
                                            'This is the actual text which the user will have to agree to in order to make a donation.',
                                            'give'
                                        )}
                                        heading={__('Agreement Text', 'give')}
                                        onButtonClick={() => setShowAgreementTextModal(!showAgreementTextModal)}
                                        isButtonActive={showAgreementTextModal}
                                    >
                                        <StyledPopover
                                            title={__('Agreement Text', 'give')}
                                            visible={showAgreementTextModal}
                                            onClose={() => setShowAgreementTextModal(false)}
                                        >
                                            <ClassicEditor
                                                id={'givewp-agreement-text'}
                                                label={__('', 'give')}
                                                content={agreementText}
                                                setContent={(value) => setAttributes({agreementText: value})}
                                            />
                                        </StyledPopover>
                                    </ControlForPopover>
                                </PanelRow>
                            )}

                            {isModalDisplay && (
                                <>
                                    <PanelRow>
                                        <TextControl
                                            label={__('Modal Heading', 'give')}
                                            value={modalHeading}
                                            onChange={(value) => setAttributes({modalHeading: value})}
                                        />
                                    </PanelRow>
                                    <PanelRow>
                                        <TextControl
                                            label={__('Modal Accept Button', 'give')}
                                            value={modalAcceptanceText}
                                            onChange={(value) => setAttributes({modalAcceptanceText: value})}
                                        />
                                    </PanelRow>
                                </>
                            )}
                        </>
                    )}
                </PanelBody>
            </InspectorControls>
        </>
    );
}

type CheckboxPlaceholderProps = {
    label: string;
    linkText: string;
    isFormDisplay: boolean;
    agreementText: string;
};

function CheckboxPlaceholder({label, linkText, isFormDisplay, agreementText}: CheckboxPlaceholderProps) {
    return (
        <div className={'givewp-terms-and-conditions'}>
            <div
                className={'givewp-terms-and-conditions-container'}
                style={{
                    display: isFormDisplay ? 'block' : 'inline-flex',
                }}
            >
                <CheckboxControl label={label} onChange={null} disabled={true} />

                {isFormDisplay && (
                    <div className={'givewp-terms-and-conditions-container__form-display'}>
                        <Markup content={agreementText} />
                    </div>
                )}

                {!isFormDisplay && (
                    <div className={'givewp-terms-and-conditions-container__link-display'}>{linkText}</div>
                )}
            </div>
        </div>
    );
}
