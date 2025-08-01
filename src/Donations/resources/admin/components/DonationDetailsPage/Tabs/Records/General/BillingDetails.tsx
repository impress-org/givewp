import { __, sprintf } from '@wordpress/i18n';
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import styles from '../styles.module.scss';
import { useFormContext } from 'react-hook-form';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';
import { useEffect, useState } from 'react';
import { getStatesForCountry, StatesConfig } from './addressUtils';

const { countries } = getDonationOptionsWindowData();

/**
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
    const { register, watch } = useFormContext();
    const { country } = watch('billingAddress');

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
                        <input id="firstName" {...register('firstName')} />
                    </AdminSectionField>
                    <AdminSectionField>
                        <label htmlFor="lastName">{__('Last name', 'give')}</label>
                        <input id="lastName" {...register('lastName')} />
                    </AdminSectionField>
                </div>

                <AdminSectionField>
                    <label htmlFor="email">{__('Email', 'give')}</label>
                    <input id="email" {...register('email')} />
                </AdminSectionField>

                <AdminSectionField>
                    <label htmlFor="country" className={styles.label}>
                        {__('Country', 'give')}
                    </label>
                    <select
                        id="country"
                        {...register('billingAddress.country')}
                    >
                        <option value="">{__('Select a country', 'give')}</option>
                        {Object.entries(countries).filter(([code]) => code !== '').map(([code, name]) => (
                            <option key={code} value={code}>
                                {name}
                            </option>
                        ))}
                    </select>
                </AdminSectionField>

                <AdminSectionField>
                    <label htmlFor="address1">{__('Address 1', 'give')}</label>
                    <input id="address1" {...register('billingAddress.address1')} />
                </AdminSectionField>

                <AdminSectionField>
                    <label htmlFor="address2">{__('Address 2', 'give')}</label>
                    <input id="address2" {...register('billingAddress.address2')} />
                </AdminSectionField>

                <AdminSectionField>
                    <label htmlFor="city">{__('City', 'give')}</label>
                    <input id="city" {...register('billingAddress.city')} />
                </AdminSectionField>

                <div className={styles.formRow}>
                    {stateConfig.showField && (
                        <AdminSectionField>
                            <label htmlFor="state" className={styles.label}>
                                {stateConfig.stateLabel}
                            </label>
                            {stateConfig.hasStates ? (
                                <select id="state" {...register('billingAddress.state')}>
                                    <option value="">{sprintf(__('Select a %s', 'give'), stateConfig.stateLabel.toLowerCase())}</option>
                                    {stateConfig.states.map((state) => (
                                        <option key={state.value} value={state.value}>
                                            {state.label}
                                        </option>
                                    ))}
                                </select>
                            ) : (
                                <input type="text" id="state" {...register('billingAddress.state')} />
                            )}
                        </AdminSectionField>
                    )}

                    <AdminSectionField>
                        <label htmlFor="zip">{__('Zip/Postal code', 'give')}</label>
                        <input id="zip" {...register('billingAddress.zip')} />
                    </AdminSectionField>
                </div>
            </div>
        </AdminSection>
    );
}
