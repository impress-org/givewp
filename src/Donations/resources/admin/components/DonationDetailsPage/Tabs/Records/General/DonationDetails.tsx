import { __ } from '@wordpress/i18n';
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import styles from '../styles.module.scss';
import { useFormContext } from 'react-hook-form';
import { CurrencyControl } from '@givewp/form-builder-library';
import { CurrencyCode } from '@givewp/form-builder-library/build/CurrencyControl/CurrencyCode';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';
import { useEffect } from 'react';

const { donationStatuses, campaignsWithForms } = getDonationOptionsWindowData();

/**
 * @unreleased
 */
export default function DonationDetails() {
    const { getValues, setValue, register, watch } = useFormContext();
    const amount = getValues('amount');
    const campaignId = watch('campaignId');
    const formId = watch('formId');
    const anonymous = watch('anonymous');
    const createdAt = watch('createdAt');

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
        <AdminSection
            title={__('Donation details', 'give')}
            description={__('This includes the donation information', 'give')}
        >
            <div>
                <div className={styles.formRow}>
                    {/* TODO: Make AdminSectionField render a label component instead of a heading */}
                    <AdminSectionField>
                        <label htmlFor="amount">{__('Amount', 'give')}</label>
                        <CurrencyControl
                            name="amount"
                            currency={amount.currency as CurrencyCode}
                            disabled={false}
                            placeholder={__('Enter amount', 'give')}
                            value={amount.value}
                            onValueChange={(value) => {
                                setValue('amount', {
                                    value: Number(value ?? 0),
                                    currency: amount.currency,
                                }, {shouldDirty: true});
                            }}
                        />
                    </AdminSectionField>
                    <AdminSectionField>
                        <label htmlFor="status">{__('Status', 'give')}</label>
                        <select id="status" name="status" {...register('status')}>
                            {donationStatuses && Object.entries(donationStatuses).map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label as string}
                                </option>
                            ))}
                        </select>
                    </AdminSectionField>
                </div>

                <AdminSectionField>
                    <label htmlFor="date">{__('Donation date and time', 'give')}</label>
                    <input
                        type="datetime-local"
                        id="date"
                        value={createdAt.date ? createdAt.date.replace(' ', 'T').slice(0, 16) : ''}
                        onChange={(e) => {
                            setValue('createdAt', {
                                ...createdAt,
                                date: e.target.value,
                            }, {shouldDirty: true});
                        }}
                    />
                </AdminSectionField>

                <div className={styles.formRow}>
                    <AdminSectionField>
                        <label htmlFor="campaignId">{__('Campaign', 'give')}</label>
                        <select id="campaignId" {...register('campaignId', {valueAsNumber: true})}>
                            {campaignsWithForms && Object.entries(campaignsWithForms).map(([campaignId, campaign]) => (
                                <option key={campaignId} value={campaignId}>
                                    {campaign.title}
                                </option>
                            ))}
                        </select>
                    </AdminSectionField>
                    <AdminSectionField>
                        <label htmlFor="formId">{__('Form', 'give')}</label>
                        <select id="formId" {...register('formId', {valueAsNumber: true})}>
                            {campaignForms && Object.entries(campaignForms).map(([formId, formTitle]) => (
                                <option key={formId} value={formId}>
                                    {formTitle}
                                </option>
                            ))}
                        </select>
                    </AdminSectionField>
                </div>

                <AdminSectionField>
                    <fieldset className={styles.radioField}>
                        <legend>{__('Anonymous donation', 'give')}</legend>
                        <div className={styles.radioOptions}>
                            <label htmlFor="anonymous-yes" className={styles.radioLabel}>
                                <input
                                    type="radio"
                                    id="anonymous-yes"
                                    value="true"
                                    defaultChecked={anonymous === true}
                                    {...register('anonymous')}
                                />
                                <span>{__('Yes, please', 'give')}</span>
                            </label>
                            <label htmlFor="anonymous-no" className={styles.radioLabel}>
                                <input
                                    type="radio"
                                    id="anonymous-no"
                                    value="false"
                                    defaultChecked={anonymous === false}
                                    {...register('anonymous')}
                                />
                                <span>{__('No, thank you', 'give')}</span>
                            </label>
                        </div>
                    </fieldset>
                </AdminSectionField>
            </div>
        </AdminSection>
    );
}
