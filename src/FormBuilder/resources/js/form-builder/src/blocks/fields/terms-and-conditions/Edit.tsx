import {
    BaseControl,
    Button,
    CheckboxControl,
    Icon,
    Modal,
    PanelBody,
    PanelRow,
    SelectControl,
    TextControl,
} from '@wordpress/components';
import {moreVertical} from '@wordpress/icons';
import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {InspectorControls} from '@wordpress/block-editor';
import {Markup} from 'interweave';

import Editor from '@givewp/form-builder/settings/email/template-options/components/editor';
import StyledPopover from '@givewp/form-builder/blocks/fields/terms-and-conditions/StyledPopover';
import GlobalSettingsLink from '@givewp/form-builder/blocks/fields/terms-and-conditions/GlobalSettingsLink';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

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

                    {useGlobalSettings && (
                        <GlobalSettingsLink
                            href={
                                '/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=display&section=terms-and-conditions'
                            }
                        />
                    )}

                    {!useGlobalSettings && (
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
                                        {label: __('Link to terms', 'give'), value: DisplayTypeEnum.SHOW_LINK_TERMS},
                                    ]}
                                />
                            </PanelRow>

                            {isLinkDisplay && (
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
                                    <BaseControl
                                        id={'give-terms-and-conditions-agreement-text'}
                                        help={__(
                                            'This is the actual text which the user will have to agree to in order to make a donation.',
                                            'give'
                                        )}
                                    >
                                        <div
                                            style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'space-between',
                                            }}
                                        >
                                            <span>{__('Agreement text')}</span>
                                            <Button
                                                style={{
                                                    color: showAgreementTextModal ? '#ffffff' : ' #1e1e1e',
                                                    background: showAgreementTextModal ? '#3D5A66' : 'transparent',
                                                    verticalAlign: 'center',
                                                }}
                                                variant={'primary'}
                                                onClick={() => setShowAgreementTextModal(true)}
                                            >
                                                <Icon icon={moreVertical} />
                                            </Button>
                                        </div>
                                    </BaseControl>
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

                            <StyledPopover
                                title={__('Agreement Text', 'give')}
                                visible={showAgreementTextModal}
                                onClose={() => setShowAgreementTextModal(false)}
                            >
                                <Editor
                                    value={agreementText}
                                    onChange={(value) => setAttributes({agreementText: value})}
                                />
                            </StyledPopover>

                            {showAgreementTextModal && (
                                <Modal
                                    title={__('Agreement Text', 'give')}
                                    onRequestClose={() => setShowAgreementTextModal(false)}
                                    shouldCloseOnClickOutside={false}
                                    style={{maxWidth: '35rem'}}
                                >
                                    <Editor
                                        value={agreementText}
                                        onChange={(value) => setAttributes({agreementText: value})}
                                    />
                                </Modal>
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
        <div style={{display: 'block'}}>
            <div
                style={{
                    display: isFormDisplay ? 'block' : 'inline-flex',
                    justifyContent: 'flex-start',
                    alignItems: 'center',
                    gap: 5,
                    border: 'none',
                }}
            >
                <CheckboxControl label={label} onChange={null} disabled={true} />

                {isFormDisplay && (
                    <div
                        style={{
                            marginTop: '1rem',
                            lineHeight: '150%',
                            maxHeight: '17.5rem',
                            minHeight: '6.5rem',
                            overflowY: 'scroll',
                            border: '1px solid var(--givewp-grey-200, #BFBFBF)',
                            borderRadius: 5,
                            padding: '0 1rem',
                            background: 'var(--givewp-shades-white, #fff)',
                        }}
                    >
                        <Markup content={agreementText} />
                    </div>
                )}

                {!isFormDisplay && (
                    <div
                        style={{
                            display: 'inline-block',
                            minWidth: 'fit-content',
                            color: 'var(--givewp-grey-80), #595959',
                            fontSize: '1rem',
                        }}
                    >
                        {linkText}
                    </div>
                )}
            </div>
        </div>
    );
}
