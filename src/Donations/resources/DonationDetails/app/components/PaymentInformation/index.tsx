import React from 'react';
import {__} from '@wordpress/i18n';

import ExternalIcon from '@givewp/components/AdminUI/Icons/ExternalIcon';
import DonationMethod from './DonationMethod';
import Legend from './Legend';

import Field from './Fields/Field';
import HiddenFields from './Fields/HiddenFields';
import AmountField from './Fields/Amount';
import FeeRecoveredField from './Fields/FeeRecovered';
import FormsField from './Fields/DonationForms';
import DatePickerField from './Fields/DatePicker';
import TimePickerField from './Fields/TimePicker';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

const {gatewayId, type, gatewayLabel} = window.GiveDonations.donationDetails;

export default function PaymentInformation() {
    return (
        <fieldset className={styles.paymentInformation}>
            <Legend title={__('Payment Information', 'give')} donationType={type} />
            <div className={styles.wrapper}>
                <div className={styles.actions}>
                    <AmountField />
                    <FeeRecoveredField />
                    <FormsField />
                    <DatePickerField />
                    <TimePickerField />

                    <Field label={__('Payment method', 'give')}>
                        <DonationMethod gatewayLabel={gatewayLabel} />
                    </Field>
                </div>
                <div className={styles.paymentGatewayLink}>
                    <span />
                    <a href={'/'}>
                        <ExternalIcon />

                        {__('View donation on gateway', 'give')}
                    </a>
                </div>
            </div>
            <HiddenFields />
        </fieldset>
    );
}
