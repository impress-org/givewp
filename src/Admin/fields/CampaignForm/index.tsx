/**
 * External dependencies
 */
import { useEffect } from 'react';
import { FieldError, useFormContext, useFormState } from 'react-hook-form';

/**
 * Internal dependencies
 */
import SelectField from './SelectField';
import styles from './styles.module.scss';
import { CampaignFormProps } from './types';
import { formatCampaignOptions, formatFormOptions } from './utils';

/**
 * @unreleased
 */
export default function CampaignForm({campaignsWithForms, campaignIdFieldName, formIdFieldName}: CampaignFormProps) {
    const {watch, setValue, control} = useFormContext();
    const {errors} = useFormState();
    const campaignId = watch(campaignIdFieldName);
    const formId = watch(formIdFieldName);

    useEffect(() => {
        if (!campaignId) {
            return;
        }

        const campaignFormIds = Object.keys(campaignsWithForms[campaignId]?.forms).map(Number);
        if (!campaignFormIds.includes(formId)) {
            setValue(formIdFieldName, Number(campaignsWithForms[campaignId]?.defaultFormId), {shouldDirty: true});
        }
    }, [campaignId]);

    const campaignForms = campaignsWithForms[campaignId]?.forms;

    const campaignOptions = formatCampaignOptions(campaignsWithForms);
    const formOptions = formatFormOptions(campaignForms);

    return (
        <div className={styles.formRow}>
            <SelectField
                name={campaignIdFieldName}
                label="Campaign"
                placeholder="Select a campaign..."
                options={campaignOptions}
                control={control}
                error={errors.campaignId as FieldError}
            />
            <SelectField
                name={formIdFieldName}
                label="Form"
                placeholder="Select a form..."
                options={formOptions}
                control={control}
                error={errors.formId as FieldError}
                isDisabled={!campaignId || formOptions.length === 0}
            />
        </div>
    )
}
