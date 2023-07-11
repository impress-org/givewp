import {
    BaseControl,
    Button,
    CheckboxControl,
    Modal,
    PanelBody,
    PanelRow,
    SelectControl,
    TextControl,
} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {InspectorControls} from '@wordpress/block-editor';

import {MenuIcon} from '@givewp/form-builder/blocks/fields/terms-and-conditions/Icon';
import Editor from '@givewp/form-builder/settings/email/template-options/components/editor';

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
    const isModalDisplay = displayType === 'showModalTerms';
    const isLinkDisplay = displayType === 'showLinkTerms';

    return (
        <>
            <Checkbox label={checkboxLabel} linkText={linkText} />

            <InspectorControls>
                <PanelBody title={__('Field Options', 'give')} initialOpen={true}>
                    <PanelRow>
                        <SelectControl
                            label={__('TERMS AND CONDITIONS', 'give')}
                            onChange={(value) => setAttributes({useGlobalSettings: !value})}
                            value={useGlobalSettings}
                            options={[
                                {label: __('Global', 'give'), value: 'true'},
                                {label: __('Customize', 'give'), value: 'false'},
                            ]}
                            help={''}
                        />
                    </PanelRow>

                    {useGlobalSettings === false && (
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
                                        {label: __('Show terms in modal', 'give'), value: 'showModalTerms'},
                                        {label: __('Show terms in form', 'give'), value: 'showFormTerms'},
                                        {label: __('Link to terms', 'give'), value: 'showLinkTerms'},
                                    ]}
                                    help={''}
                                />
                            </PanelRow>

                            <PanelRow>
                                <TextControl
                                    label={__('Link Text', 'give')}
                                    value={linkText}
                                    onChange={(value) => setAttributes({linkText: value})}
                                />
                            </PanelRow>

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
                                            {__('Agreement text')}
                                            <Button
                                                style={{background: 'transparent', verticalAlign: 'center'}}
                                                variant={'primary'}
                                                onClick={() => setShowAgreementTextModal(true)}
                                            >
                                                <MenuIcon />
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

                            {showAgreementTextModal && (
                                <Modal
                                    title={__('Agreement Text', 'give')}
                                    onRequestClose={() => setShowAgreementTextModal(false)}
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

function Checkbox({label, linkText}) {
    return (
        <div
            style={{
                display: 'flex',
                justifyContent: 'flex-start',
                alignItems: 'center',
                gap: 5,
                width: 'fit-content',
                border: 'none',
            }}
        >
            <CheckboxControl label={label} onChange={null} disabled={true} />

            <span
                style={{
                    minWidth: 'fit-content',
                    color: 'var(--givewp-grey-80)',
                    fontSize: '1rem',
                }}
            >
                {linkText}
            </span>
        </div>
    );
}
