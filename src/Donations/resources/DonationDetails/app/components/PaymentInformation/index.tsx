import React, {useContext, useState} from 'react';
import {useFormContext, useWatch} from 'react-hook-form';
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
import {data} from '../../config/data';
import useFormOptions from '../../hooks/useFormOptions';
import styles from './style.module.scss';
import {formatCurrency} from '../../utilities/formatter';
import {defaultFormValues} from '../../utilities/defaultFormValues';

/**
 *
 * @unreleased
 */
export default function PaymentInformation() {
    const methods = useFormContext();
    const options = useFormOptions();

    const {register, setValue, reset, control} = methods;
    const confirmActionDialog = useContext(ModalContext);
    const [readableDateValue, setReadableDateValue] = useState<string>(
        format(new Date(data.createdAt.date), 'MMMM d, yyyy')
    );
    const [readableTimeValue, setReadableTimeValue] = useState<string>(format(new Date(data.createdAt.date), 'h:mm a'));
    const [focused, setFocused] = useState<boolean>(false);
    const [showDatePicker, setShowDatePicker] = useState<boolean>(false);
    const [showTimePicker, setShowTimePicker] = useState<boolean>(false);

    const [amountUpdating, setAmountUpdating] = useState<number>(defaultFormValues.amount);

    const amount = useWatch({name: 'amount'});
    const feeAmountRecovered = useWatch({name: 'feeAmountRecovered'});

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
                    <input hidden {...register('amount')} />
                    <ActionContainer
                        label={__('Total Donation', 'give')}
                        display={formatCurrency(Number(amount), data.amount.currency)}
                        type={'amount'}
                        showEditDialog={() =>
                            confirmActionDialog(
                                __(' Edit total donation', 'give'),
                                <CurrencyInputField
                                    defaultValue={amountUpdating}
                                    currency={data.amount.currency}
                                    handleCurrencyChange={(value) => setAmountUpdating(value)}
                                    label={__('Total donation', 'give')}
                                    placeholder={__('Enter an amount', 'give')}
                                />,
                                () => {
                                    setValue('amount', amountUpdating);
                                },
                                () => {},
                                __('Set Donation Amount', 'give'),
                                __('Changes made will not be billed to the donor', 'give')
                            )
                        }
                    />
                    {/*<ActionContainer*/}
                    {/*    label={__('Fee recovered', 'give')}*/}
                    {/*    display={formatCurrency(Number(feeAmountRecovered), data.amount.currency)}*/}
                    {/*    type={'amount'}*/}
                    {/*    showEditDialog={() =>*/}
                    {/*        confirmActionDialog(*/}
                    {/*            __(' Edit fee recovered', 'give'),*/}
                    {/*            <CurrencyInputField*/}
                    {/*                {...register('feeAmountRecovered')}*/}
                    {/*                control={control}*/}
                    {/*                name={'feeAmountRecovered'}*/}
                    {/*                defaultValue={data.feeAmountRecovered}*/}
                    {/*                currency={data.amount.currency}*/}
                    {/*                handleCurrencyChange={handleCurrencyChange}*/}
                    {/*                label={__('Fee Recovered', 'give')}*/}
                    {/*                placeholder={__('Enter fee amount', 'give')}*/}
                    {/*                type={'text'}*/}
                    {/*            />,*/}
                    {/*            () => console.log('feeAmountRecovered', feeAmountRecovered),*/}

                    {/*            () => reset({feeAmountRecovered: defaultFormValues.feeAmountRecovered}),*/}

                    {/*            __('Set Fee Recovered', 'give'),*/}
                    {/*            __('Changes made will not be billed to the donor', 'give')*/}
                    {/*        )*/}
                    {/*    }*/}
                    {/*/>*/}
                    <ActionContainer
                        label={__('Donation form', 'give')}
                        display={
                            <SearchSelector
                                name={'form'}
                                placeholder={__('Search for a donation form', 'give')}
                                options={options}
                                defaultLabel={data.formTitle}
                            />
                        }
                        type={'text'}
                    />
                    <ActionContainer
                        label={__('Donation date', 'give')}
                        display={readableDateValue}
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
                        display={readableTimeValue}
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
                        display={<DonationMethod gateway={data?.gatewayLabel} gatewayId={data?.gatewayId} />}
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
