// Vendor dependencies
import {useState} from 'react';
import PropTypes from 'prop-types';
import moment from 'moment';
// react-dates dependencies
import 'react-dates/initialize';
import {DateRangePicker} from 'react-dates';
import 'react-dates/lib/css/_datepicker.css';

import styles from './style.module.scss';

const PeriodSelector = ({period = {startDate: null, endDate: null}, setDates = () => {}}) => {
    const [focusedInput, setFocusedInput] = useState(null);

    const icon = (
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g opacity="0.501465">
                <path opacity="0.3" d="M3.75 6H14.25V4.5H3.75V6Z" fill="black" />
                <path
                    fillRule="evenodd"
                    clipRule="evenodd"
                    d="M13.5 3H14.25C15.075 3 15.75 3.675 15.75 4.5V15C15.75 15.825 15.075 16.5 14.25 16.5H3.75C2.9175 16.5 2.25 15.825 2.25 15L2.2575 4.5C2.2575 3.675 2.9175 3 3.75 3H4.5V1.5H6V3H12V1.5H13.5V3ZM6.75 9.75V8.25H5.25V9.75H6.75ZM14.25 15H3.75V7.5H14.25V15ZM3.75 6H14.25V4.5H3.75V6ZM12.75 8.25V9.75H11.25V8.25H12.75ZM9.75 8.25H8.25V9.75H9.75V8.25Z"
                    fill="black"
                />
            </g>
        </svg>
    );

    return (
        <div className={styles.periodSelector} key={focusedInput}>
            <div className={styles.icon}>{icon}</div>
            <div className={styles.datepicker}>
                <DateRangePicker
                    noBorder={true}
                    startDate={period.startDate}
                    startDateId="givewp-logs-start"
                    endDate={period.endDate}
                    hideKeyboardShortcutsPanel={true}
                    endDateId="givewp-logs-end"
                    onDatesChange={({startDate, endDate}) => {
                        setDates(startDate, endDate);
                    }}
                    focusedInput={focusedInput}
                    onFocusChange={(newFocus) => {
                        setFocusedInput(newFocus);
                    }}
                    isOutsideRange={(day) => moment().diff(day) < 0}
                    numberOfMonths={1}
                />
            </div>
        </div>
    );
};

PeriodSelector.propTypes = {
    // Time period
    period: PropTypes.object.isRequired,
    // SetDates function
    setDates: PropTypes.func.isRequired,
};

export default PeriodSelector;
