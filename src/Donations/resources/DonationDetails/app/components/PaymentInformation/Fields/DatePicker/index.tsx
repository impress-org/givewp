import Field from '../Field';
import 'react-dates/initialize';
import 'react-dates/lib/css/_datepicker.css';
import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {DayPickerSingleDateController} from 'react-dates';
import styles from './style.module.scss';
import {useFormContext, useWatch} from 'react-hook-form';
import moment from 'moment';
import {format} from 'date-fns';
import {CalendarProps} from '../types';

export default function DatePickerField() {
    const watchedDate = useWatch({name: 'createdAt'});

    const [isCalendarOpen, setIsCalendarOpen] = useState<boolean>(false);

    return (
        <Field label={__('Donation date', 'give')} editable onEdit={() => setIsCalendarOpen(!isCalendarOpen)}>
            {isCalendarOpen && <Calendar closeCalendar={() => setIsCalendarOpen(!isCalendarOpen)} />}
            <span>{format(new Date(watchedDate), 'MMMM, dd, yyyy')}</span>
        </Field>
    );
}

export function Calendar({closeCalendar}: CalendarProps) {
    const {setValue, getValues} = useFormContext();
    const createdAt = getValues('createdAt');

    const [focused, setFocused] = useState<boolean>(false);
    const [date, setDate] = useState<object | null>(moment(createdAt, 'yyyy-MM-dd'));

    const handleDateChange = (newDate) => {
        setDate(newDate);

        const formattedDate = newDate.format('yyyy-MM-D');
        const preservedTimeValue = new Date(createdAt).toLocaleTimeString();

        const newDateObject = new Date(`${formattedDate} ${preservedTimeValue}`);

        const validFormFieldValue = newDateObject.toISOString();

        setValue('createdAt', validFormFieldValue, {shouldDirty: true});

        closeCalendar();
    };

    return (
        <div className={styles.calendarPosition}>
            <DayPickerSingleDateController
                date={date}
                onDateChange={handleDateChange}
                focused={focused}
                numberOfMonths={1}
                isOutsideRange={() => false}
                onBlur={close}
                displayFormat={() => 'DD/MM/YYYY'}
            />
        </div>
    );
}
