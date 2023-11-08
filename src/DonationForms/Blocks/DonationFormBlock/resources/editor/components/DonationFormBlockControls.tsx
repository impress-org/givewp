import {ExternalLink, PanelBody, PanelRow, SelectControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';

export default function DonationFormBlockControls({
    isResolving,
    formOptions,
    formId,
    formFormat,
    setAttributes,
    openFormButton,
    showOpenFormButton,
}) {
    return (
        <InspectorControls>
            <PanelBody title={__('Form Settings', 'give')} initialOpen={true}>
                <PanelRow>
                    {!isResolving && formOptions.length === 0 ? (
                        <p>{__('No forms were found using the GiveWP form builder.', 'give')}</p>
                    ) : (
                        <SelectControl
                            label={__('Choose a donation form', 'give')}
                            value={formId ?? ''}
                            options={[
                                // add a disabled selector manually
                                ...[{value: '', label: __('Select...', 'give'), disabled: true}],
                                ...formOptions,
                            ]}
                            onChange={(newFormId) => {
                                setAttributes({id: newFormId});
                            }}
                        />
                    )}
                </PanelRow>
                <PanelRow>
                    <SelectControl
                        label={__('Form Format', 'give')}
                        value={formFormat}
                        options={[
                            {
                                label: __('Full Form', 'give'),
                                value: 'fullForm',
                            },
                            {
                                label: __('New Tab', 'give'),
                                value: 'newTab',
                            },
                            {
                                label: __('Modal', 'give'),
                                value: 'modal',
                            },
                        ]}
                        onChange={(value) => {
                            setAttributes({formFormat: value});
                        }}
                    />
                </PanelRow>
                {showOpenFormButton && (
                    <PanelRow>
                        <TextControl
                            label={__('Open Form Button', 'give')}
                            value={openFormButton}
                            onChange={(value) => {
                                setAttributes({openFormButton: value});
                            }}
                        />
                    </PanelRow>
                )}
                <PanelRow>
                    {formId && (
                        <ExternalLink
                            href={`/wp-admin/edit.php?post_type=give_forms&page=givewp-form-builder&donationFormID=${formId}`}
                        >
                            {__('Edit donation form', 'give')}
                        </ExternalLink>
                    )}
                </PanelRow>
            </PanelBody>
        </InspectorControls>
    );
}
