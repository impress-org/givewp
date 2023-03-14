import {useContext, useState} from 'react';

import {useWatch} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import moment from 'moment';
import {DayPickerSingleDateController} from 'react-dates';
import 'react-dates/initialize';
import 'react-dates/lib/css/_datepicker.css';

import ActionContainer from './ActionContainer';
import ExternalIcon from '@givewp/components/AdminUI/Icons/ExternalIcon';
import PaypalIcon from '@givewp/components/AdminUI/Icons/PaypalIcon';
import {TextInputField} from '@givewp/components/AdminUI/FormElements';

import {ModalContext} from '@givewp/components/AdminUI/FormPage';

import {PaymentInformation} from '../types';

import styles from './style.module.scss';
import BlueExitIcon from '@givewp/components/AdminUI/Icons/BlueExitIcon';
import StatusSelector from '@givewp/components/AdminUI/StatusSelector';

function FormSelect() {
    return (
        <select>
            <option>Form</option>
        </select>
    );
}

export const donationStatusOptions = [
    {
        value: 'publish',
        label: __('Completed', 'give'),
    },
    {
        value: 'pending',
        label: __('Pending', 'give'),
    },
    {
        value: 'processing',
        label: __('Processing', 'give'),
    },
    {
        value: 'refunded',
        label: __('Refunded', 'give'),
    },
    {
        value: 'revoked',
        label: __('Revoked', 'give'),
    },
    {
        value: 'failed',
        label: __('Failed', 'give'),
    },
    {
        value: 'cancelled',
        label: __('Cancelled', 'give'),
    },
    {
        value: 'abandoned',
        label: __('Abandoned', 'give'),
    },
    {
        value: 'preApproval',
        label: __('Pre-approved', 'give'),
    },
];

function Legend({title}) {
    return (
        <div className={styles.legend}>
            <legend>
                <h2>{title}</h2>
            </legend>
            <div className={styles.paymentType}>
                <p className="badge__label" id="badgeId-48">
                    Subscriber
                </p>
                <StatusSelector options={donationStatusOptions} />
            </div>
        </div>
    );
}

export default function PaymentInformation({register, setValue}: PaymentInformation) {
    const confirmActionDialog = useContext(ModalContext);
    const [dateObject, setDateObject] = useState<object>();
    const [readableDate, setReadableDate] = useState<string>(moment(dateObject).format('LL'));
    const [focused, setFocused] = useState<boolean>(false);
    const [showDatePicker, setShowDatePicker] = useState<boolean>(false);
    const [showTimePicker, setShowTimePicker] = useState<boolean>(false);

    const totalDonation = useWatch({
        name: 'totalDonation',
    });

    const feeAmount = useWatch({
        name: 'feeAmount',
    });

    const toggleDatePicker = () => {
        setShowDatePicker(!showDatePicker);
        setFocused(focused);
    };

    const toggleTimePicker = () => {
        setShowTimePicker(!showTimePicker);
    };

    const handleDateChange = (selectedDate) => {
        const formattedDate = moment(selectedDate).format('LL');
        setReadableDate(formattedDate);
        setDateObject(selectedDate);
        setValue('createdAt', new Date(selectedDate).toString());
        setShowDatePicker(!showDatePicker);
    };

    const DatePickerFormField = () => {
        return (
            <div className={styles.calendarPosition}>
                <DayPickerSingleDateController
                    date={dateObject}
                    onDateChange={(selectedDate, event) => handleDateChange(selectedDate)}
                    focused={true}
                    onFocusChange={({focused}) => {
                        setFocused(focused);
                    }}
                />
            </div>
        );
    };

    const TimePickerFormField = () => {
        return (
            <div className={styles.timePickerPosition}>
                <label hidden htmlFor={'give-payment-time-hour'}>
                    {__('Payment time by the hour')}
                </label>
                <input
                    id={'give-payment-time-hour'}
                    name="give-payment-time-hour"
                    type={'number'}
                    step="1"
                    min={0}
                    max={12}
                />

                <>&#x3A;</>

                <label hidden htmlFor={'give-payment-time-minute'}>
                    {__('Payment time by the minute')}
                </label>
                <input
                    id={'give-payment-time-minute'}
                    name="give-payment-time-minute"
                    type={'number'}
                    min={0}
                    max={59}
                />

                <select id="give-payment-time-am-pm" name="ampm">
                    <option value="am">AM</option>
                    <option value="pm">PM</option>
                </select>

                <div onClick={toggleTimePicker}>
                    <BlueExitIcon />
                </div>
            </div>
        );
    };

    return (
        <fieldset className={styles.paymentInformation}>
            <Legend title={__('Payment Information', 'give')} />
            <div className={styles.actions}>
                <ActionContainer
                    label={__('Total Donation', 'give')}
                    value={totalDonation}
                    type={'amount'}
                    showEditDialog={() =>
                        confirmActionDialog(
                            __(' Edit total donation', 'give'),
                            <TextInputField
                                {...register('totalDonation')}
                                name={'totalDonation'}
                                label={__('Total Donations', 'give')}
                                asCurrencyField
                            />,
                            null,
                            __('Set Donation Amount', 'give'),
                            __('Changes made will not be billed to the donor', 'give')
                        )
                    }
                />
                <ActionContainer
                    label={__('Fee recovered', 'give')}
                    value={feeAmount}
                    type={'amount'}
                    showEditDialog={() =>
                        confirmActionDialog(
                            __(' Edit fee amount', 'give'),
                            <TextInputField
                                {...register('feeAmount')}
                                name={'feeAmount'}
                                label={__('Fee Amount', 'give')}
                                asCurrencyField
                            />,
                            null,
                            __('Set Fee Amount', 'give'),
                            __('Changes made will not be billed to the donor', 'give')
                        )
                    }
                />
                <ActionContainer label={__('Donation form', 'give')} value={<FormSelect />} type={'text'} />
                <ActionContainer
                    label={__('Donation date', 'give')}
                    value={readableDate}
                    type={'text'}
                    showEditDialog={toggleDatePicker}
                    formField={showDatePicker && <DatePickerFormField />}
                />
                <ActionContainer
                    label={__('Donation time', 'give')}
                    value={'10:00 am'}
                    type={'text'}
                    showEditDialog={toggleTimePicker}
                    formField={showTimePicker && <TimePickerFormField />}
                />
                <ActionContainer
                    label={__('Payment method', 'give')}
                    value={
                        <div className={styles.paymentMethod}>
                            <PaypalIcon />
                            {__('Paypal', 'give')}
                        </div>
                    }
                    type={'text'}
                />
                <a href={'/'}>
                    <ExternalIcon />

                    {__('View donation on gateway', 'give')}
                </a>
            </div>
        </fieldset>
    );
}
