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

function Legend({title}) {
    return (
        <div className={styles.legend}>
            <legend>
                <h2>{title}</h2>
            </legend>
            <div className={styles.paymentType}>
                <div>Subscriber</div>
                <StatusSelect />
            </div>
        </div>
    );
}

function StatusSelect() {
    return (
        <select>
            <option>Completed</option>
        </select>
    );
}

function FormSelect() {
    return (
        <select>
            <option>Form</option>
        </select>
    );
}

export default function PaymentInformation({register, createdAt, setValue}: PaymentInformation) {
    const confirmActionDialog = useContext(ModalContext);
    const [dateObject, setDateObject] = useState();
    const [readableDate, setReadableDate] = useState(moment(createdAt).format('LL'));
    const [focused, setFocused] = useState(false);
    const [showDatePicker, setShowDatePicker] = useState(false);
    const [showTimePicker, setShowTimePicker] = useState(false);

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

    const handleDateChange = (selectedDate, event) => {
        const formattedDate = moment(selectedDate).format('LL');
        setReadableDate(formattedDate);
        setValue('createdAt', new Date(selectedDate).toString());
        setShowDatePicker(!showDatePicker);
    };

    const DatePickerFormField = () => {
        return (
            <div className={styles.calendarPosition}>
                <DayPickerSingleDateController
                    date={dateObject}
                    onDateChange={(selectedDate, event) => handleDateChange(selectedDate, event)}
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
                            __('Save Changes', 'give'),
                            __('Changes made will not be billed to the donor', 'give')
                        )
                    }
                />
                <ActionContainer
                    label={__('Fee amount', 'give')}
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
                            __('Save Changes', 'give'),
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
                        <>
                            <PaypalIcon />
                            {__('Paypal', 'give')}
                        </>
                    }
                    type={'text'}
                />
                <a href={'/'}>
                    <ExternalIcon />

                    {__('View Payment gateway', 'give')}
                </a>
            </div>
        </fieldset>
    );
}
