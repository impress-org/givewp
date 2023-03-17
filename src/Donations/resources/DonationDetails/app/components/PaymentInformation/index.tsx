import {useContext, useState} from 'react';

import {useFormContext} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import {format, parse} from 'date-fns';

import {ModalContext} from '@givewp/components/AdminUI/FormPage';

import ExternalIcon from '@givewp/components/AdminUI/Icons/ExternalIcon';
import {CurrencyInputField} from '@givewp/components/AdminUI/FormElements';
import StatusSelector from '@givewp/components/AdminUI/StatusSelector';
import SearchSelector from '@givewp/components/AdminUI/SearchSelector';
import DonationType from './DonationType';
import DonationMethod from './DonationMethod';
import ActionContainer from './ActionContainer';

import {FormTemplateProps} from '../FormTemplate/types';
import TimePickerFormField from './TimePickerFormField';
import DatePickerFormField from './DatePickerFormField';

import styles from './style.module.scss';

const tempDonationFormOptions = [
    {value: 1, label: 'donation form 1'},
    {value: 2, label: 'donation form 2'},
    {value: 3, label: 'donation form 3'},
    {value: 4, label: 'donation form 4'},
];

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

/**
 *
 * @unreleased
 */
function Legend({title, donationType}) {
    return (
        <div className={styles.legend}>
            <legend>
                <h2>{title}</h2>
            </legend>
            <div className={styles.paymentType}>
                <DonationType donationType={donationType} />
                <StatusSelector options={donationStatusOptions} />
            </div>
        </div>
    );
}

/**
 *
 * @unreleased
 */
export default function PaymentInformation({data}: FormTemplateProps) {
    const methods = useFormContext();
    const {register, setValue, getValues, reset} = methods;

    const confirmActionDialog = useContext(ModalContext);
    const [readableDateValue, setReadableDateValue] = useState<string>(
        format(new Date(data?.createdAt), 'MMMM d, yyyy')
    );
    const [readableTimeValue, setReadableTimeValue] = useState<string>(format(new Date(data?.createdAt), 'h:mm a'));
    const [focused, setFocused] = useState<boolean>(false);
    const [showDatePicker, setShowDatePicker] = useState<boolean>(false);
    const [showTimePicker, setShowTimePicker] = useState<boolean>(false);
    const [showSearchSelector, setShowSearchSelector] = useState<boolean | null>(null);

    const [totalDonation, setTotalDonation] = useState<string>(getValues('totalDonation'));
    const [feeRecovered, setFeeRecovered] = useState<string>(getValues('feeRecovered'));

    const retrieveUpdatedTotalDonation = () => {
        setTotalDonation(getValues('totalDonation'));
    };
    const retrieveUpdatedFeeRecovered = () => {
        setFeeRecovered(getValues('feeRecovered'));
    };

    const resetDefaultValue = (inputName: string, defaultValue: unknown | any) => {
        reset({inputName: defaultValue});
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
                        value={totalDonation}
                        type={'amount'}
                        showEditDialog={() =>
                            confirmActionDialog(
                                __(' Edit total donation', 'give'),
                                <CurrencyInputField
                                    {...register('totalDonation')}
                                    name={'totalDonation'}
                                    label={__('Total Donations', 'give')}
                                    placeholder={__('Enter total amount', 'give')}
                                    type={'text'}
                                />,
                                () => retrieveUpdatedTotalDonation(),
                                () => resetDefaultValue('totalDonation', totalDonation),
                                __('Set Donation Amount', 'give'),
                                __('Changes made will not be billed to the donor', 'give')
                            )
                        }
                    />
                    <ActionContainer
                        label={__('Fee recovered', 'give')}
                        value={feeRecovered}
                        type={'amount'}
                        showEditDialog={() =>
                            confirmActionDialog(
                                __(' Edit fee recovered', 'give'),
                                <CurrencyInputField
                                    {...register('feeRecovered')}
                                    name={'feeRecovered'}
                                    label={__('Fee Recovered', 'give')}
                                    placeholder={__('Enter fee amount', 'give')}
                                    type={'text'}
                                />,
                                () => retrieveUpdatedFeeRecovered(),
                                () => resetDefaultValue('feeRecovered', feeRecovered),
                                __('Set Fee Recovered', 'give'),
                                __('Changes made will not be billed to the donor', 'give')
                            )
                        }
                    />
                    <ActionContainer
                        label={__('Donation form', 'give')}
                        value={
                            <SearchSelector
                                options={tempDonationFormOptions}
                                openSelector={showSearchSelector}
                                setOpenSelector={setShowSearchSelector}
                                defaultLabel={data.form.name}
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
                        value={<DonationMethod gateway={data?.gateway} gatewayId={data?.gatewayId} />}
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
