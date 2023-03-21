import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {parseInt} from 'lodash';
import {format} from 'date-fns';

import BlueExitIcon from '@givewp/components/AdminUI/Icons/BlueExitIcon';

import {TimePickerProps} from '../types';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */
export default function TimePickerFormField({
    showFormField,
    toggleFormField,
    parsedTime,
    handleFormField,
}: TimePickerProps) {
    const [hours, setHours] = useState(format(parsedTime, parsedTime.getHours() >= 12 ? 'h' : 'h'));
    const [minutes, setMinutes] = useState(parsedTime.getMinutes());
    const [amPm, setAmPm] = useState(format(parsedTime, 'a'));
    return (
        <div className={styles.timePickerPosition}>
            <label hidden htmlFor={'give-payment-time-hour'}>
                {__('Payment time by the hour')}
            </label>
            <input
                onChange={(event) => {
                    setHours(event.target.value);
                    handleFormField(event.target.value, minutes, amPm);
                }}
                id={'give-payment-time-hour'}
                name="give-payment-time-hour"
                defaultValue={hours}
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
                onChange={(event) => {
                    setMinutes(parseInt(event.target.value));
                    handleFormField(hours, parseInt(event.target.value), amPm);
                }}
                id={'give-payment-time-minute'}
                name="give-payment-time-minute"
                defaultValue={minutes}
                type={'number'}
                min={0}
                max={59}
            />

            <label hidden htmlFor={'give-payment-time-am-pm'}>
                {__('Time of day am or pm')}
            </label>
            <select
                onChange={(event) => {
                    setAmPm(event.target.value);
                }}
                id="give-payment-time-am-pm"
                name="ampm"
                defaultValue={amPm}
            >
                <option value="AM">AM</option>
                <option value="PM">PM</option>
            </select>

            <div role={'button'} aria-pressed={showFormField} onClick={toggleFormField}>
                <BlueExitIcon />
            </div>
        </div>
    );
}
