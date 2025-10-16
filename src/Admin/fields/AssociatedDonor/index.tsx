import {__} from '@wordpress/i18n';
import {useFormContext, useFormState} from 'react-hook-form';
import {SelectOption} from '@givewp/admin/types';
import useDonorAsyncSelectOptions from './useDonorAsyncSelectOptions';
import AsyncSelectOption from '@givewp/admin/fields/AsyncSelectOption';

type AssociatedDonorProps = {
    name: string;
    mode: 'test' | 'live';
    label: string;
    description: string;
}

/**
 * @since 4.11.0 use AsyncSelectOption
 * @since 4.9.0 Add error prop to all AdminSectionField components
 * @since 4.8.0 updated to async donor dropdown
 * @since 4.6.0
 */
export default function AssociatedDonor({name, mode, label, description}: AssociatedDonorProps) {
    const {watch, setValue} = useFormContext();
    const {errors} = useFormState();
    const donorId = watch(name);

    const {selectedOption, loadOptions, mapOptionsForMenu, error} = useDonorAsyncSelectOptions(donorId, {mode});

    const handleChange = (selectedOption: SelectOption) => {
        setValue(name, selectedOption?.value ?? null, {shouldDirty: true});
    };

    return (
        <AsyncSelectOption
            name={name}
            label={label}
            description={description}
            handleChange={handleChange}
            selectedOption={selectedOption}
            loadOptions={loadOptions}
            mapOptionsForMenu={mapOptionsForMenu}
            isLoadingError={error}
            errorMessage={errors[name]?.message as string}
            searchPlaceholder={__('Search for a donor...', 'give')}
            loadingMessage={__('Loading donors...', 'give')}
            loadingError={__('Error loading donors. Please try again.', 'give')}
            ariaLabel={__('Select a donor', 'give')}
            noOptionsMessage={__('No donors found.', 'give')}
        />
    );
}
