import {__} from '@wordpress/i18n';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {ExternalLink, PanelBody, PanelRow, SelectControl, TextControl} from '@wordpress/components';
import {Fragment, useCallback, useEffect, useState} from '@wordpress/element';
import useFormOptions from './hooks/useFormOptions';
import ConfirmButton from './components/ConfirmButton';
import Logo from './components/Logo';
import {BlockEditProps} from '@wordpress/blocks';
import ReactSelect from 'react-select';
import BlockPreview from './components/BlockPreview';

import './styles/index.scss';

/**
 * @since 3.0.0
 */
export default function Edit({clientId, attributes, setAttributes}: BlockEditProps<any>) {
    const {formId, blockId, formFormat, openFormButton} = attributes;
    const {formOptions, isResolving} = useFormOptions();
    const [showPreview, setShowPreview] = useState<boolean>(!!formId);

    const showOpenFormButton = formFormat === 'reveal' || formFormat === 'modal';

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
        <Fragment>
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
                            label={__('Form Format', 'give')}
                            value={formFormat}
                            options={[
                                {
                                    label: __('Full Form', 'give'),
                                    value: 'full',
                                },
                                {
                                    label: __('Reveal', 'give'),
                                    value: 'reveal',
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

            {/*block preview*/}
            <div {...useBlockProps()}>
                {formId && showPreview ? (
                    <BlockPreview
                        clientId={clientId}
                        formId={formId}
                        formFormat={formFormat}
                        openFormButton={openFormButton}
                    />
                ) : (
                    <div className="givewp-form-block--container">
                        <Logo />

                        <div className="givewp-form-block__select--container">
                            <label htmlFor="formId" className="givewp-form-block__select--label">
                                {__('Choose a donation form', 'give')}
                            </label>

                            <ReactSelect
                                classNamePrefix="givewp-form-block__select"
                                name="formId"
                                inputId="formId"
                                value={getDefaultFormId()}
                                placeholder={
                                    isResolving ? __('Loading Donation Forms...', 'give') : __('Select...', 'give')
                                }
                                onChange={(option) => {
                                    if (option) {
                                        setAttributes({formId: option.value});
                                    }
                                }}
                                noOptionsMessage={() => (
                                    <p>{__('No forms were found using the GiveWP form builder.', 'give')}</p>
                                )}
                                options={formOptions}
                                loadingMessage={() => <>{__('Loading Donation Forms...', 'give')}</>}
                                isLoading={isResolving}
                                theme={(theme) => ({
                                    ...theme,
                                    colors: {
                                        ...theme.colors,
                                        primary: '#27ae60',
                                    },
                                })}
                                styles={{
                                    input: (provided, state) => ({
                                        ...provided,
                                        height: '3rem',
                                    }),
                                    option: (provided, state) => ({
                                        ...provided,
                                        paddingTop: '0.8rem',
                                        paddingBottom: '0.8rem',
                                        fontSize: '1rem',
                                    }),
                                    control: (provided, state) => ({
                                        ...provided,
                                        fontSize: '1rem',
                                    }),
                                }}
                            />
                        </div>

                        <ConfirmButton formId={formId} enablePreview={() => setShowPreview(true)} />
                    </div>
                )}
            </div>
        </Fragment>
    );
}
