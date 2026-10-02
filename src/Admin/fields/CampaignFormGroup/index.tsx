/**
 * External dependencies
 */
import { FieldError, useFormContext, useFormState } from 'react-hook-form';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import styles from './styles.module.scss';
import AsyncSelectOption from '../AsyncSelectOption';
import useCampaignAsyncSelectOptions from './useCampaignAsyncSelectOptions';
import useFormAsyncSelectOptions from './useFormAsyncSelectOptions';
import { SelectOption } from '@givewp/admin/types';

type CampaignFormGroupProps = {
    campaignIdFieldName: string;
    formIdFieldName: string;
}

/**
 * @since TBD Add a "No campaign" option, which clears the form and lists standalone forms.
 * @since TBD Keep the form select mounted when only the form changes, so it keeps focus and its loaded options.
 * @since 4.11.0
 */
export default function CampaignFormGroup({ campaignIdFieldName, formIdFieldName }: CampaignFormGroupProps) {
    const noCampaignOption: SelectOption = { value: 0, label: __('No campaign', 'give') };
    const { watch, setValue } = useFormContext();
    const { errors } = useFormState();
    const campaignId = watch(campaignIdFieldName);
    const formId = watch(formIdFieldName);

    const { selectedOption: campaignSelectedOption, loadOptions: campaignLoadOptions, mapOptionsForMenu: campaignMapOptionsForMenu, error: campaignError } = useCampaignAsyncSelectOptions(campaignId);
    const { selectedOption: formSelectedOption, loadOptions: formLoadOptions, mapOptionsForMenu: formMapOptionsForMenu, error: formError } = useFormAsyncSelectOptions(formId, campaignId);

    /* A campaign carries its default form's title, which labels the form until the form's own record loads. */
    const campaign = campaignSelectedOption?.record;
    const defaultFormOption: SelectOption | null =
        formId && campaign?.defaultFormId === formId ? { value: formId, label: campaign.defaultFormTitle } : null;

    const handleCampaignChange = (selectedOption: SelectOption) => {
        setValue(campaignIdFieldName, selectedOption?.value || null, { shouldDirty: true });
        setValue(formIdFieldName, selectedOption?.record?.defaultFormId ?? null, { shouldDirty: true });
    };

    const handleFormChange = (selectedOption: SelectOption) => {
        setValue(formIdFieldName, selectedOption?.value ?? null, { shouldDirty: true });
    };


    return (
        <div className={styles.formRow}>
            <AsyncSelectOption
                name={campaignIdFieldName}
                label={__('Campaign', 'give')}
                handleChange={handleCampaignChange}
                selectedOption={campaignId ? campaignSelectedOption : noCampaignOption}
                loadOptions={campaignLoadOptions}
                mapOptionsForMenu={(options) => [noCampaignOption, ...campaignMapOptionsForMenu(options)]}
                isLoadingError={campaignError}
                errorMessage={errors[campaignIdFieldName]?.message as string}
                searchPlaceholder={__('Search for a campaign...', 'give')}
                loadingMessage={__('Loading campaigns...', 'give')}
                loadingError={__('Error loading campaigns. Please try again.', 'give')}
                ariaLabel={__('Select a campaign', 'give')}
                noOptionsMessage={__('No campaigns found.', 'give')}
            />
            <AsyncSelectOption
                key={`${campaignId}`}
                name={formIdFieldName}
                label={__('Form', 'give')}
                handleChange={handleFormChange}
                selectedOption={formSelectedOption ?? defaultFormOption}
                loadOptions={formLoadOptions}
                mapOptionsForMenu={formMapOptionsForMenu}
                isLoadingError={formError}
                errorMessage={errors[formIdFieldName]?.message as string}
                searchPlaceholder={__('Search for a form...', 'give')}
                loadingMessage={__('Loading forms...', 'give')}
                loadingError={__('Error loading forms. Please try again.', 'give')}
                ariaLabel={__('Select a form', 'give')}
                noOptionsMessage={__('No forms found.', 'give')}
            />
        </div>
    )
}
