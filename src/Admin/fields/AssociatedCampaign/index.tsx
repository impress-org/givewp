import {__} from '@wordpress/i18n';
import {useFormContext, useFormState} from 'react-hook-form';
import {SelectOption} from '@givewp/admin/types';
import useCampaignAsyncSelectOptions from './useCampaignAsyncSelectOptions';
import AsyncSelectOption from '@givewp/admin/fields/AsyncSelectOption';

/**
 * @unreleased
 */
export default function CampaignSelector({name, label, description}) {
    const {watch, setValue} = useFormContext();
    const {errors} = useFormState();
    const campaignId = watch(name);

    const {selectedOption, loadOptions, mapOptionsForMenu, error} = useCampaignAsyncSelectOptions(campaignId);

    const handleChange = (selectedOption: SelectOption | null) => {
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
            searchPlaceholder={__('Search for a campaign...', 'give')}
            loadingMessage={__('Loading campaigns...', 'give')}
            loadingError={__('Error loading campaigns. Please try again.', 'give')}
            ariaLabel={__('Select a campaign', 'give')}
            noOptionsMessage={__('No campaigns found.', 'give')}
        >
            {/* Modal */}
        </AsyncSelectOption>
    );
}
