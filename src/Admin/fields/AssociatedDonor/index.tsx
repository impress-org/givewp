import AdminSection, {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import {__} from '@wordpress/i18n';
import {useFormContext, useFormState} from 'react-hook-form';
import {AsyncPaginate} from 'react-select-async-paginate';
import styles from './styles.module.scss';
import {DonorOption} from './types';
import {useDonorAsyncSelect} from './useDonorAsyncSelect';

/**
 * @unreleased add mode prop
 * @since 4.9.0 Add error prop to all AdminSectionField components
 * @since 4.8.0 updated to async donor dropdown
 * @since 4.6.0
 */
export default function AssociatedDonor({mode}: {mode: 'test' | 'live'}) {
    const {watch, setValue} = useFormContext();
    const {errors} = useFormState();
    const donationDonorId = watch('donorId');

    const {selectedOption, loadOptions, mapOptionsForMenu, error} = useDonorAsyncSelect(donationDonorId || null, mode);

    const handleDonorChange = (selectedOption: DonorOption | null) => {
        setValue('donorId', selectedOption?.value ?? null, {shouldDirty: true});
    };

    return (
        <AdminSectionField error={errors.donorId?.message as string}>
            <label htmlFor="donorId">{__('Donor', 'give')}</label>
            <p className={styles.fieldDescription}>{__('Link the donation to the selected donor', 'give')}</p>
            {error ? (
                <div role="alert" style={{color: 'var(--givewp-red-500)', fontSize: '0.875rem'}}>
                    {__('Error loading donors. Please try again.', 'give')}
                </div>
            ) : (
                <AsyncPaginate
                    inputId="donorId"
                    className={styles.searchableSelect}
                    classNamePrefix="searchableSelect"
                    value={selectedOption}
                    loadOptions={loadOptions}
                    mapOptionsForMenu={mapOptionsForMenu}
                    onChange={handleDonorChange}
                    debounceTimeout={600}
                    placeholder={__('Search for a donor...', 'give')}
                    loadingMessage={() => __('Loading donors...', 'give')}
                    noOptionsMessage={() => __('No donors found.', 'give')}
                    aria-label={__('Select a donor', 'give')}
                />
            )}
        </AdminSectionField>
    );
}
