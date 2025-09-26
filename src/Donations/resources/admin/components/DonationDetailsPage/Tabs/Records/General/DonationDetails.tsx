/**
 * External dependencies
 */
import {CurrencyControl} from '@givewp/form-builder-library';
import {CurrencyCode} from '@givewp/form-builder-library/build/CurrencyControl/CurrencyCode';
import cx from 'classnames';
import {useEffect} from 'react';

/**
 * WordPress dependencies
 */
import {__} from '@wordpress/i18n';
import {useFormContext, useFormState} from 'react-hook-form';

/**
 * Internal dependencies
 */
import AdminSection, {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import {formatDateTimeLocal} from '@givewp/components/AdminDetailsPage/utils';
import {getDonationOptionsWindowData} from '@givewp/donations/utils';
import styles from '../styles.module.scss';
// TODO: Move to shared components
import PhoneInput from '@givewp/donors/admin/components/Inputs/Phone';
import StatusField from '@givewp/admin/fields/Status';

const {donationStatuses, campaignsWithForms, intlTelInputSettings} = getDonationOptionsWindowData();

/**
 * @since 4.10.0 replace Status field with admin Status component
 * @since 4.9.0 Add error prop to all AdminSectionField components
 * @since 4.6.0
 */
export default function DonationDetails() {
    const {getValues, setValue, register, watch, setError} = useFormContext();
    const {errors} = useFormState();
    const amount = getValues('amount');
    const campaignId = watch('campaignId');
    const formId = watch('formId');
    const status = watch('status');
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
                    <AdminSectionField error={errors.amount?.message as string}>
                        <label htmlFor="amount">{__('Amount', 'give')}</label>
                        <CurrencyControl
                            name="amount"
                            currency={amount.currency as CurrencyCode}
                            disabled={false}
                            placeholder={__('Enter amount', 'give')}
                            value={amount.value}
                            onValueChange={(value) => {
                                setValue(
                                    'amount',
                                    {
                                        amount: Number(value ?? 0),
                                        currency: amount.currency,
                                    },
                                    {shouldDirty: true}
                                );
                            }}
                        />
                    </AdminSectionField>
                    <AdminSectionField error={errors.status?.message as string}>
                        <StatusField statusOptions={donationStatuses} />
                    </AdminSectionField>
                </div>

                <AdminSectionField error={errors.createdAt?.message as string}>
                    <label htmlFor="date">{__('Donation date and time', 'give')}</label>
                    <input
                        type="datetime-local"
                        id="date"
                        value={formatDateTimeLocal(createdAt?.date)}
                        onChange={(e) => {
                            setValue(
                                'createdAt',
                                {
                                    date: formatDateTimeLocal(e.target.value),
                                    timezone: createdAt?.timezone,
                                    timezone_type: createdAt?.timezone_type,
                                },
                                {shouldDirty: true}
                            );
                        }}
                    />
                </AdminSectionField>

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

                {/* TODO: Add Fund field */}

                <AdminSectionField error={errors.comment?.message as string}>
                    <label htmlFor="comment">{__('Donor comment', 'give')}</label>
                    <textarea
                        id="comment"
                        {...register('comment')}
                        rows={3}
                        placeholder={__('Add a comment', 'give')}
                    />
                </AdminSectionField>

                <AdminSectionField error={errors.company?.message as string}>
                    <label htmlFor="company">{__('Company', 'give')}</label>
                    <input id="company" {...register('company')} placeholder={__('Enter company', 'give')} />
                </AdminSectionField>

                <AdminSectionField error={errors.phone?.message as string}>
                    <label htmlFor="phone">{__('Phone', 'give')}</label>
                    <PhoneInput
                        id="phone"
                        value={watch('phone')}
                        onChange={(value) => setValue('phone', value, {shouldDirty: true})}
                        onError={(errorMessage) => setError('phone', {message: errorMessage})}
                        intlTelInputSettings={intlTelInputSettings}
                    />
                </AdminSectionField>

                <AdminSectionField error={errors.anonymous?.message as string}>
                    <fieldset className={styles.radioField}>
                        <legend>{__('Anonymous donation', 'give')}</legend>
                        <div className={styles.radioOptions}>
                            <label htmlFor="anonymous-yes" className={styles.radioLabel}>
                                <input
                                    type="radio"
                                    id="anonymous-yes"
                                    value="true"
                                    {...register('anonymous', {
                                        setValueAs: (value) => value.toString(),
                                    })}
                                />
                                <span>{__('Yes', 'give')}</span>
                            </label>
                            <label htmlFor="anonymous-no" className={styles.radioLabel}>
                                <input
                                    type="radio"
                                    id="anonymous-no"
                                    value="false"
                                    {...register('anonymous', {
                                        setValueAs: (value) => value.toString(),
                                    })}
                                />
                                <span>{__('No', 'give')}</span>
                            </label>
                        </div>
                    </fieldset>
                </AdminSectionField>
            </div>
        </AdminSection>
    );
}
