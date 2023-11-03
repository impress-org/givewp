import {__} from '@wordpress/i18n';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {ExternalLink, PanelBody, PanelRow, SelectControl, TextControl} from '@wordpress/components';
import {useCallback, useEffect, useState} from '@wordpress/element';
import {BlockEditProps} from '@wordpress/blocks';
import BlockPreview from './components/BlockPreview';
import DonationFormSelector from './components/DonationFormSelector';
import useFormOptions from './hooks/useFormOptions';

import './styles/index.scss';

/**
 * @since 3.0.0
 */
export default function Edit({clientId, attributes, setAttributes}: BlockEditProps<any>) {
    const {formId, blockId, displayStyle, openFormButton} = attributes;
    const [showPreview, setShowPreview] = useState<boolean>(!!formId);
    const {formOptions, isResolving} = useFormOptions();

    const showOpenFormButton = displayStyle === 'link' || displayStyle === 'modal';

    useEffect(() => {
        if (!blockId) {
            setAttributes({blockId: clientId});
        }
    }, []);

    const getDefaultFormId = useCallback(() => {
        if (!isResolving && formOptions.length > 0) {
            return formId && formOptions?.find(({value}) => value === formId);
        }
    }, [isResolving, formId, JSON.stringify(formOptions)]);

    return (
        <>
            {/*block controls*/}
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
                                    setAttributes({formId: newFormId});
                                }}
                            />
                        )}
                    </PanelRow>
                    <PanelRow>
                        <SelectControl
                            label={__('Display style', 'give')}
                            value={displayStyle}
                            options={[
                                {
                                    label: __('On page', 'give'),
                                    value: 'onPage',
                                },
                                {
                                    label: __('Link to new page', 'give'),
                                    value: 'link',
                                },
                                {
                                    label: __('Modal', 'give'),
                                    value: 'modal',
                                },
                            ]}
                            onChange={(value) => {
                                setAttributes({displayStyle: value});
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

            {/*block preview*/}
            <div {...useBlockProps()}>
                {formId && showPreview ? (
                    <BlockPreview
                        clientId={clientId}
                        formId={formId}
                        displayStyle={displayStyle}
                        openFormButton={openFormButton}
                    />
                ) : (
                    <DonationFormSelector
                        formId={formId}
                        getDefaultFormId={getDefaultFormId}
                        setShowPreview={setShowPreview}
                        setAttributes={setAttributes}
                    />
                )}
            </div>
        </>
    );
}
