import {useContext, useState} from 'react';

import {useWatch} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import moment from 'moment';
import {SingleDatePicker, DayPickerSingleDateController} from 'react-dates';
import 'react-dates/initialize';
import 'react-dates/lib/css/_datepicker.css';

import ActionContainer from './ActionContainer';
import ExternalIcon from '@givewp/components/AdminUI/Icons/ExternalIcon';
import PaypalIcon from '@givewp/components/AdminUI/Icons/PaypalIcon';
import {TextInputField} from '@givewp/components/AdminUI/FormElements';

import {ModalContext} from '@givewp/components/AdminUI/FormPage';

import {PaymentInformation} from '../types';

import styles from './style.module.scss';

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

export default function PaymentInformation({register, createdAt}: PaymentInformation) {
    const confirmActionDialog = useContext(ModalContext);
    const [dateObject, setDateObject] = useState();
    const [readableDate, setReadableDate] = useState(moment(createdAt).format('LL'));
    const [focused, setFocused] = useState(false);
    const [showDatePicker, setShowDatePicker] = useState(false);

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

    const handleDateChange = (selectedDate) => {
        const formattedDate = moment(selectedDate).format('LL');
        setReadableDate(formattedDate);
        setShowDatePicker(!showDatePicker);
    };

    const DatePickerFormField = () => {
        return (
            <div className={styles.absolutePosition}>
                <DayPickerSingleDateController
                    date={dateObject}
                    onDateChange={(selectedDate) => handleDateChange(selectedDate)}
                    focused={true}
                    onFocusChange={({focused}) => {
                        setFocused(focused);
                    }}
                />
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
                    showEditDialog={null}
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
