import {useState} from 'react';
import {__} from '@wordpress/i18n';

import {useFormContext, useWatch} from 'react-hook-form';
import {format, parse} from 'date-fns';

import Field from '../Field';
import {AmpmField, NumberField} from './inputFields';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */
export default function TimePickerField() {
    const timeObject = useWatch({name: 'createdAt'});

    const {setValue} = useFormContext();

    const [showFields, setShowFields] = useState<boolean>(false);

    const setNewFormFieldValue = (newDateObject) => {
        setShowFields(false);

        if (newDateObject) {
            setValue('createdAt', newDateObject, {shouldDirty: true});
        }
    };

    return (
        <Field label={__('Donation time', 'give')} editable onEdit={() => setShowFields(!showFields)}>
            {showFields ? (
                <div className={styles.timePickerPosition}>
                    <TimeFields initialTime={timeObject} isOpen={showFields} closeFields={setNewFormFieldValue} />
                </div>
            ) : (
                <span>{format(timeObject, 'h:mm a')}</span>
            )}
        </Field>
    );
}

/**
 *
 * @unreleased
 */
type TimeFieldProps = {
    isOpen: boolean;
    closeFields: (newDate: Date | null) => void;
    initialTime: Date;
};

export function TimeFields({isOpen, closeFields, initialTime}: TimeFieldProps) {
    const [hours, setHours] = useState<number>(Number(format(initialTime, initialTime.getHours() >= 12 ? 'h' : 'h')));
    const [minutes, setMinutes] = useState<number>(Number(format(initialTime, String(initialTime.getMinutes()))));
    const [meridiem, setMeridiem] = useState<string>(format(initialTime, 'h:mm a').split(' ')[1]);

    const confirmFieldValues = (setData: boolean) => {
        const dateString = String(`${hours}:${minutes} ${meridiem}`);
        const newDateObject = parse(dateString, 'h:m a', initialTime);

        closeFields(setData && newDateObject);
    };

    return (
        <>
            <NumberField
                id={'give-payment-time-hour'}
                label={__('Payment time by the minute')}
                setState={setHours}
                state={hours}
                min={0}
                max={12}
            />
            <>&#x3A;</>
            <NumberField
                id={'give-payment-time-minute'}
                label={__('Payment time by the minute')}
                setState={setMinutes}
                state={minutes}
                min={0}
                max={59}
            />
            <AmpmField state={meridiem} setState={setMeridiem} />

            <div className={styles.timeFieldActions}>
                <span
                    className={styles.confirmSelection}
                    role={'button'}
                    aria-pressed={isOpen}
                    onClick={() => confirmFieldValues(true)}
                >
                    {__('Set', 'give')}
                </span>
                <span
                    className={styles.cancelSelection}
                    role={'button'}
                    aria-pressed={isOpen}
                    onClick={() => confirmFieldValues(false)}
                >
                    {__('Cancel', 'give')}
                </span>
            </div>
        </>
    );
}
