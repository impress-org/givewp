import {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import { __ } from '@wordpress/i18n';

import styles from './styles.module.scss';
import { useEffect } from 'react';
import { useFormContext, useFormState } from 'react-hook-form';
import { CampaignFormProps } from './types';

export default function CampaignForm({campaignId, formId, campaignsWithForms}: CampaignFormProps) {
    const {register, setValue} = useFormContext();
    const {errors} = useFormState();

    useEffect(() => {
        if (!campaignId) {
            return;
        }

        const campaignFormIds = Object.keys(campaignsWithForms[campaignId]?.forms).map(Number);
        if (!campaignFormIds.includes(formId)) {
            setValue('formId', campaignsWithForms[campaignId]?.defaultFormId, {shouldDirty: true});
        }
    }, [campaignId]);

    const campaignForms = campaignsWithForms[campaignId]?.forms;

    return (
        <div className={styles.formRow}>
            <AdminSectionField error={errors.campaignId?.message as string}>
                <label htmlFor="campaignId">{__('Campaign', 'give')}</label>
                <select id="campaignId" {...register('campaignId', {valueAsNumber: true})}>
                    {campaignsWithForms &&
                        Object.entries(campaignsWithForms).map(([campaignId, campaign]) => (
                            <option key={campaignId} value={campaignId}>
                                {campaign?.title}
                            </option>
                        ))}
                </select>
            </AdminSectionField>
            <AdminSectionField error={errors.formId?.message as string}>
                <label htmlFor="formId">{__('Form', 'give')}</label>
                <select id="formId" {...register('formId', {valueAsNumber: true})}>
                    {campaignForms &&
                        Object.entries(campaignForms).map(([formId, formTitle]) => (
                            <option key={formId} value={formId}>
                                {formTitle}
                            </option>
                        ))}
                </select>
            </AdminSectionField>
        </div>
    )
}
