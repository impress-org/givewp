import {__} from '@wordpress/i18n';
import ReactSelect from 'react-select';
import {reactSelectStyles, reactSelectThemeStyles} from '../styles/reactSelectStyles';
import ConfirmButton from './ConfirmButton';
import useFormOptions from '../hooks/useFormOptions';
import logo from '../images/givewp-logo.svg';

import '../styles/index.scss';

/**
 * @unreleased
 */
export default function DonationFormSelector({defaultFormId, id, setShowPreview, setAttributes}) {
    const {formOptions, isResolving} = useFormOptions();

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
                    value={defaultFormId}
                    placeholder={isResolving ? __('Loading Donation Forms...', 'give') : __('Select...', 'give')}
                    onChange={(option) => {
                        if (option) {
                            setAttributes({id: Number(option.value)});
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
            <ConfirmButton formId={id} enablePreview={() => setShowPreview(true)} />
        </div>
    );
}
