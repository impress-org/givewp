import {__} from '@wordpress/i18n';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {Fragment} from '@wordpress/element';
import useFormOptions from './hooks/useFormOptions';
import ConfirmButton from './components/ConfirmButton';
import Logo from './components/Logo';
import {BlockEditProps} from "@wordpress/blocks";
import ReactSelect from 'react-select';
import {useCallback} from "react";


/**
 * @since 1.0.0
 */
export default function Edit({attributes, setAttributes}: BlockEditProps<any>) {
    const {formId} = attributes;
    const {formOptions, isResolving} = useFormOptions();

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
                        {!isResolving && formOptions.length === 0 ?
                            <p>{__('No forms were found using the GiveWP form builder.', 'give')}</p> : (
                                <SelectControl
                                    label={__('Choose a donation form', 'give')}
                                    value={formId ?? ''}
                                    options={[
                                        // add a disabled selector manually
                                        ...[{value: '', label: __('Select...', 'give'), disabled: true}],
                                        ...formOptions
                                    ]}
                                    onChange={(newFormId) => {
                                        setAttributes({formId: newFormId});
                                    }}
                                />
                            )}
                    </PanelRow>
                </PanelBody>
            </InspectorControls>

            {/*block preview*/}
            <div {...useBlockProps()}>
                <div className="givewp-form-block--container">
                    <Logo/>

                    <div className="givewp-form-block__select--container">
                        <label
                            htmlFor="formId"
                            className="givewp-form-block__select--label">{__('Choose a donation form', 'give')}
                        </label>

                        <ReactSelect
                            classNamePrefix="givewp-form-block__select"
                            name="formId"
                            inputId="formId"
                            value={getDefaultFormId()}
                            placeholder={isResolving ? __('Loading Donation Forms...', 'give') : __('Select...', 'give')}
                            onChange={(option) => {
                                if (option) {
                                    setAttributes({formId: option.value});
                                }
                            }}
                            noOptionsMessage={() =>
                                <p>{__('No forms were found using the GiveWP form builder.', 'give')}</p>}
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
                                    height: '3rem'
                                }),
                                option: (provided, state) => ({
                                    ...provided,
                                    paddingTop: '0.8rem',
                                    paddingBottom: '0.8rem',
                                    fontSize: '1rem'
                                }),
                                control: (provided, state) => ({
                                    ...provided,
                                    fontSize: '1rem'
                                })
                            }}
                        />

                    </div>

                    <ConfirmButton/>
                </div>
            </div>
        </Fragment>
    );
}
