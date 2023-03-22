import {useContext, useState} from 'react';

import {useFormContext} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import {format, parse} from 'date-fns';

import {ModalContext} from '@givewp/components/AdminUI/FormPage';

import ExternalIcon from '@givewp/components/AdminUI/Icons/ExternalIcon';
import {CurrencyInputField} from '@givewp/components/AdminUI/FormElements';
import SearchSelector from '@givewp/components/AdminUI/SearchSelector';
import DonationMethod from './DonationMethod';
import ActionContainer from './ActionContainer';

import TimePickerFormField from './TimePickerFormField';
import DatePickerFormField from './DatePickerFormField';
import Legend from './Legend';

import {defaultFormValues} from '../../utilities/defaultFormValues';
import {formatCurrency} from '../../utilities/formatter';
import {data} from '../../config/data';

import useResetFieldValue from '../../hooks/useResetFieldValue';
import useFormOptions from '../../hooks/useFormOptions';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */
export default function PaymentInformation() {
    const methods = useFormContext();
    const {register, setValue, getValues, reset} = methods;

    const confirmActionDialog = useContext(ModalContext);
    const [readableDateValue, setReadableDateValue] = useState<string>(
        format(new Date(data.createdAt.date), 'MMMM d, yyyy')
    );
    const [readableTimeValue, setReadableTimeValue] = useState<string>(format(new Date(data.createdAt.date), 'h:mm a'));
    const [focused, setFocused] = useState<boolean>(false);
    const [showDatePicker, setShowDatePicker] = useState<boolean>(false);
    const [showTimePicker, setShowTimePicker] = useState<boolean>(false);

    const [formattedDonationAmount, setFormattedDonationAmount] = useState<string>(
        formatCurrency(defaultFormValues.amount, data.amount.currency)
    );
    const [formattedFeeAmountRecovered, setFormattedFeeAmountRecovered] = useState<string>(
        formatCurrency(defaultFormValues.feeAmountRecovered, data.amount.currency)
    );

    const updateDonationAmount = () => {
        setFormattedDonationAmount(formatCurrency(parseInt(getValues('amount')), data.amount.currency));
    };
    const updateFeeRecovered = () => {
        setFormattedFeeAmountRecovered(formatCurrency(parseInt(getValues('feeAmountRecovered')), data.amount.currency));
    };

    const toggleDatePicker = () => {
        setShowDatePicker(!showDatePicker);
        setFocused(focused);
    };

    const toggleTimePicker = () => {
        setShowTimePicker(!showTimePicker);
    };

    const handleDateChange = (selectedDate) => {
        const dateObjectWithDate = new Date(selectedDate);
        const dateObjectWithTime = parse(readableTimeValue, 'h:mm aa', new Date());

        setReadableDateValue(format(dateObjectWithDate, 'MMMM d, yyyy'));

        const combinedDateObject = new Date(dateObjectWithTime.getTime());

        combinedDateObject.setDate(dateObjectWithDate.getDate());

        const formDataValue = format(combinedDateObject, 'yyyy-MM-dd HH:mm:ss');

        setValue('createdAt', formDataValue, {shouldDirty: true});

        setShowDatePicker(!showDatePicker);
    };

    const handleTimeChange = (hours, minutes, ampm) => {
        // Convert to 24 hour value
        if (ampm === 'am' && hours !== 12) {
            hours += 12;
        } else if (ampm === 'pm' && hours === 12) {
            hours = 0;
        }

        // Create new Date objects
        const dateObjectWithDate = new Date(readableDateValue);
        const dateObjectWithTime = parse(`${hours}:${minutes} ${ampm}`, 'h:mm aa', new Date());

        setReadableTimeValue(format(dateObjectWithTime, 'h:mm aa'));

        // Combine Date objects
        const combinedDateObject = new Date(dateObjectWithTime.getTime());

        combinedDateObject.setDate(dateObjectWithDate.getDate());

        const formDataValue = format(combinedDateObject, 'yyyy-MM-dd HH:mm:ss');

        // Update createdAt value as one Date object
        setValue('createdAt', formDataValue, {shouldDirty: true});
    };

    return (
        <fieldset className={styles.paymentInformation}>
            <Legend title={__('Payment Information', 'give')} donationType={data.type} />
            <div className={styles.wrapper}>
                <div className={styles.actions}>
                    <ActionContainer
                        label={__('Total Donation', 'give')}
                        value={formattedDonationAmount}
                        type={'amount'}
                        showEditDialog={() =>
                            confirmActionDialog(
                                __(' Edit total donation', 'give'),
                                <CurrencyInputField
                                    {...register('amount')}
                                    currency={data.amount.currency}
                                    name={'amount'}
                                    label={__('Total Donations', 'give')}
                                    placeholder={__('Enter total amount', 'give')}
                                    type={'text'}
                                />,
                                () => updateDonationAmount(),
                                () => useResetFieldValue('amount'),
                                __('Set Donation Amount', 'give'),
                                __('Changes made will not be billed to the donor', 'give')
                            )
                        }
                    />
                    <ActionContainer
                        label={__('Fee recovered', 'give')}
                        value={formattedFeeAmountRecovered}
                        type={'amount'}
                        showEditDialog={() =>
                            confirmActionDialog(
                                __(' Edit fee recovered', 'give'),
                                <CurrencyInputField
                                    {...register('feeAmountRecovered')}
                                    currency={data.amount.currency}
                                    name={'feeAmountRecovered'}
                                    label={__('Fee Recovered', 'give')}
                                    placeholder={__('Enter fee amount', 'give')}
                                    type={'text'}
                                />,
                                () => updateFeeRecovered(),
                                () => useResetFieldValue('feeAmountRecovered'),
                                __('Set Fee Recovered', 'give'),
                                __('Changes made will not be billed to the donor', 'give')
                            )
                        }
                    />
                    <ActionContainer
                        label={__('Donation form', 'give')}
                        value={
                            <SearchSelector
                                name={'form'}
                                placeholder={__('Search for a donation form', 'give')}
                                options={useFormOptions}
                                defaultLabel={data.formTitle}
                            />
                        }
                        type={'text'}
                    />
                    <ActionContainer
                        label={__('Donation date', 'give')}
                        value={readableDateValue}
                        type={'text'}
                        showEditDialog={toggleDatePicker}
                        formField={
                            showDatePicker && (
                                <DatePickerFormField setFocused={setFocused} handleFormField={handleDateChange} />
                            )
                        }
                    />
                    <ActionContainer
                        label={__('Donation time', 'give')}
                        value={readableTimeValue}
                        type={'text'}
                        showEditDialog={toggleTimePicker}
                        formField={
                            showTimePicker && (
                                <TimePickerFormField
                                    showFormField={showTimePicker}
                                    toggleFormField={toggleTimePicker}
                                    handleFormField={handleTimeChange}
                                    parsedTime={parse(readableTimeValue, 'h:mm a', new Date())}
                                />
                            )
                        }
                    />
                    <ActionContainer
                        label={__('Payment method', 'give')}
                        value={<DonationMethod gateway={data?.gatewayLabel} gatewayId={data?.gatewayId} />}
                        type={'text'}
                    />
                </div>
                <div className={styles.paymentGatewayLink}>
                    <span />
                    <a href={'/'}>
                        <ExternalIcon />

                        {__('View donation on gateway', 'give')}
                    </a>
                </div>
            </div>
        </fieldset>
    );
}
