import AdminSection, {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import ErrorField from '@givewp/components/AdminUI/ErrorField';
import {getDonationOptionsWindowData} from '@givewp/donations/utils';
import {__, sprintf} from '@wordpress/i18n';
import {useEffect, useState} from 'react';
import {useFormContext, useFormState} from 'react-hook-form';
import styles from '../styles.module.scss';
import {StatesConfig, getStatesForCountry} from './addressUtils';

const {countries} = getDonationOptionsWindowData();

/**
 * @unreleased Add error field components to all input fields
 * @since 4.6.0
 */
export default function BillingDetails() {
    const [stateConfig, setStateConfig] = useState<StatesConfig>({
        hasStates: false,
        states: [],
        stateLabel: __('State', 'give'),
        isRequired: false,
        showField: true,
    });
    const {register, watch} = useFormContext();
    const {errors} = useFormState();
    const {country} = watch('billingAddress');

    useEffect(() => {
        if (country) {
            const config = getStatesForCountry(country);
            setStateConfig(config);
        }
    }, [country]);

    return (
        <AdminSection
            title={__('Billing details', 'give')}
            description={__('This includes the billing name, email and address', 'give')}
        >
            <div>
                <div className={styles.formRow}>
                    <AdminSectionField>
                        <label htmlFor="firstName">{__('First name', 'give')}</label>
                        <ErrorField error={errors.firstName}>
                            <input id="firstName" {...register('firstName')} />
                        </ErrorField>
                    </AdminSectionField>
                    <AdminSectionField>
                        <label htmlFor="lastName">{__('Last name', 'give')}</label>
                        <ErrorField error={errors.lastName}>
                            <input id="lastName" {...register('lastName')} />
                        </ErrorField>
                    </AdminSectionField>
                </div>

                <AdminSectionField>
                    <label htmlFor="email">{__('Email', 'give')}</label>
                    <ErrorField error={errors.email}>
                        <input id="email" {...register('email')} />
                    </ErrorField>
                </AdminSectionField>

                <AdminSectionField>
                    <label htmlFor="country" className={styles.label}>
                        {__('Country', 'give')}
                    </label>
                    <ErrorField error={(errors.billingAddress as any)?.country}>
                        <select id="country" {...register('billingAddress.country')}>
                            <option value="">{__('Select a country', 'give')}</option>
                            {Object.entries(countries)
                                .filter(([code]) => code !== '')
                                .map(([code, name]) => (
                                    <option key={code} value={code}>
                                        {name}
                                    </option>
                                ))}
                        </select>
                    </ErrorField>
                </AdminSectionField>

                <AdminSectionField>
                    <label htmlFor="address1">{__('Address 1', 'give')}</label>
                    <ErrorField error={(errors.billingAddress as any)?.address1}>
                        <input id="address1" {...register('billingAddress.address1')} />
                    </ErrorField>
                </AdminSectionField>

                <AdminSectionField>
                    <label htmlFor="address2">{__('Address 2', 'give')}</label>
                    <ErrorField error={(errors.billingAddress as any)?.address2}>
                        <input id="address2" {...register('billingAddress.address2')} />
                    </ErrorField>
                </AdminSectionField>

                <AdminSectionField>
                    <label htmlFor="city">{__('City', 'give')}</label>
                    <ErrorField error={(errors.billingAddress as any)?.city}>
                        <input id="city" {...register('billingAddress.city')} />
                    </ErrorField>
                </AdminSectionField>

                <div className={styles.formRow}>
                    {stateConfig.showField && (
                        <AdminSectionField>
                            <label htmlFor="state" className={styles.label}>
                                {stateConfig.stateLabel}
                            </label>
                            <ErrorField error={(errors.billingAddress as any)?.state}>
                                {stateConfig.hasStates ? (
                                    <select id="state" {...register('billingAddress.state')}>
                                        <option value="">
                                            {sprintf(__('Select a %s', 'give'), stateConfig.stateLabel.toLowerCase())}
                                        </option>
                                        {stateConfig.states.map((state) => (
                                            <option key={state.value} value={state.value}>
                                                {state.label}
                                            </option>
                                        ))}
                                    </select>
                                ) : (
                                    <input type="text" id="state" {...register('billingAddress.state')} />
                                )}
                            </ErrorField>
                        </AdminSectionField>
                    )}

                    <AdminSectionField>
                        <label htmlFor="zip">{__('Zip/Postal code', 'give')}</label>
                        <ErrorField error={(errors.billingAddress as any)?.zip}>
                            <input id="zip" {...register('billingAddress.zip')} />
                        </ErrorField>
                    </AdminSectionField>
                </div>
            </div>
        </AdminSection>
    );
}
