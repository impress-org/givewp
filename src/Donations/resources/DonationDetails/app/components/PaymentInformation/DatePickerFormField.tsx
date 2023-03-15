import {DayPickerSingleDateController} from 'react-dates';
import 'react-dates/initialize';
import 'react-dates/lib/css/_datepicker.css';

import {DatePickerProps} from './types';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */
export default function DatePickerFormField({setFocused, handleFormField}: DatePickerProps) {
    return (
        <div className={styles.calendarPosition}>
            <DayPickerSingleDateController
                onDateChange={(selectedDate) => handleFormField(selectedDate)}
                focused={true}
                onFocusChange={({focused}) => {
                    setFocused(focused);
                }}
            />
        </div>
    );
}
