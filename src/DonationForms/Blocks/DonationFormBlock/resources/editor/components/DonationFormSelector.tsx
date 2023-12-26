import {useState} from 'react';
import usePostState from '../hooks/usePostState';
import {dispatch} from '@wordpress/data';
import {__} from '@wordpress/i18n';
import ReactSelect from 'react-select';
import {reactSelectStyles, reactSelectThemeStyles} from '../styles/reactSelectStyles';
import logo from '../images/givewp-logo.svg';

import '../styles/index.scss';
import type {FormOption} from '../hooks/useFormOptions';

// @ts-ignore
const savePost = () => dispatch('core/editor').savePost();

/**
 * @since 3.2.1
 */
type DonationFormSelectorProps = {
    formOptions: FormOption[];
    isResolving: boolean;
    handleSelect: (id: number) => void;
}

/**
 * @since 3.2.0
 */
export default function DonationFormSelector({formOptions, isResolving, handleSelect}: DonationFormSelectorProps) {
    const [selectedForm, setSelectedForm] = useState<number>(null);
    const form = formOptions.find(form => form.value === selectedForm);
    const {isSaving, isDisabled} = usePostState();

    return (
        <div className="givewp-donation-form-selector">
            <img className="givewp-donation-form-selector__logo" src={logo} alt="givewp-logo" />
            <div className="givewp-donation-form-selector__select">
                <label htmlFor="formId" className="givewp-donation-form-selector__label">
                    {__('Choose a donation form', 'give')}
                </label>

                <ReactSelect
                    name="formId"
                    inputId="formId"
                    value={form}
                    placeholder={isResolving ? __('Loading Donation Forms...', 'give') : __('Select...', 'give')}
                    onChange={(option) => {
                        if (option) {
                            setSelectedForm(option.value);
                        }
                    }}
                    noOptionsMessage={() => <p>{__('No forms were found using the GiveWP form builder.', 'give')}</p>}
                    options={formOptions}
                    loadingMessage={() => <>{__('Loading Donation Forms...', 'give')}</>}
                    isLoading={isResolving}
                    theme={reactSelectThemeStyles}
                    styles={reactSelectStyles}
                />
            </div>

            <button
                className="givewp-donation-form-selector__submit"
                type="button"
                disabled={isSaving || isDisabled || !selectedForm}
                onClick={() => {
                    handleSelect(selectedForm);
                    savePost();
                }}
            >
                {__('Confirm', 'give')}
            </button>
        </div>
    );
}
