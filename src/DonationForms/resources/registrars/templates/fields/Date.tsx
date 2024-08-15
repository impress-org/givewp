import DatePicker from 'react-datepicker';
import {format, parse} from 'date-fns';

import {DateProps} from '@givewp/forms/propTypes';
import 'react-datepicker/dist/react-datepicker.css';
import styles from '../styles.module.scss';

export default function Date({
    Label,
    ErrorMessage,
    description,
    dateFormat = 'yyyy/mm/dd',
    fieldError,
    inputProps,
}: DateProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {useFormContext, useWatch} = window.givewp.form.hooks;
    const {setValue} = useFormContext();
    const value = useWatch({name: inputProps.name});

    dateFormat = dateFormat.replace('mm', 'MM');

    return (
        <label className={styles.dateField}>
            <Label />
            {description && <FieldDescription description={description} />}
            <div className={styles.dateFieldInputContainer}>
                <input type="hidden" {...inputProps} />
                <DatePicker
                    placeholderText={dateFormat}
                    ariaInvalid={fieldError ? 'true' : 'false'}
                    dateFormat={dateFormat}
                    selected={value ? parse(value, dateFormat, new window.Date()) : null}
                    onChange={(date) => setValue(inputProps.name, date ? format(date, dateFormat) : '')}
                />

                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path
                        fillRule="evenodd"
                        clipRule="evenodd"
                        d="M17 2C17 1.44772 16.5523 1 16 1C15.4477 1 15 1.44772 15 2V3H9V2C9 1.44772 8.55229 1 8 1C7.44772 1 7 1.44772 7 2V3.00163C6.52454 3.00489 6.10898 3.01472 5.74818 3.04419C5.18608 3.09012 4.66938 3.18868 4.18404 3.43597C3.43139 3.81947 2.81947 4.43139 2.43598 5.18404C2.18868 5.66937 2.09012 6.18608 2.0442 6.74817C1.99998 7.28936 1.99999 7.95373 2 8.75869V17.2413C1.99999 18.0463 1.99998 18.7106 2.0442 19.2518C2.09012 19.8139 2.18868 20.3306 2.43598 20.816C2.81947 21.5686 3.43139 22.1805 4.18404 22.564C4.66938 22.8113 5.18608 22.9099 5.74818 22.9558C6.28937 23 6.95372 23 7.75868 23H16.2413C17.0463 23 17.7106 23 18.2518 22.9558C18.8139 22.9099 19.3306 22.8113 19.816 22.564C20.5686 22.1805 21.1805 21.5686 21.564 20.816C21.8113 20.3306 21.9099 19.8139 21.9558 19.2518C22 18.7106 22 18.0463 22 17.2413V8.75868C22 7.95372 22 7.28936 21.9558 6.74817C21.9099 6.18608 21.8113 5.66937 21.564 5.18404C21.1805 4.43139 20.5686 3.81947 19.816 3.43597C19.3306 3.18868 18.8139 3.09012 18.2518 3.04419C17.891 3.01472 17.4755 3.00489 17 3.00163V2ZM7 6V5.00176C6.55447 5.00489 6.20463 5.01357 5.91104 5.03755C5.47262 5.07337 5.24842 5.1383 5.09202 5.21799C4.7157 5.40973 4.40974 5.7157 4.21799 6.09202C4.1383 6.24842 4.07337 6.47262 4.03755 6.91104C4.00078 7.36113 4 7.94342 4 8.8V9H20V8.8C20 7.94342 19.9992 7.36113 19.9624 6.91104C19.9266 6.47262 19.8617 6.24842 19.782 6.09202C19.5903 5.7157 19.2843 5.40973 18.908 5.21799C18.7516 5.1383 18.5274 5.07337 18.089 5.03755C17.7954 5.01357 17.4455 5.00489 17 5.00176V6C17 6.55228 16.5523 7 16 7C15.4477 7 15 6.55228 15 6V5H9V6C9 6.55228 8.55229 7 8 7C7.44772 7 7 6.55228 7 6ZM12.4718 12.1183C12.797 12.2923 13 12.6312 13 13V17H13.25C13.8023 17 14.25 17.4477 14.25 18C14.25 18.5523 13.8023 19 13.25 19H10.75C10.1977 19 9.75 18.5523 9.75 18C9.75 17.4477 10.1977 17 10.75 17H11V14.8661C10.547 15.1282 9.96229 14.9962 9.66793 14.5547C9.36158 14.0952 9.48576 13.4743 9.94528 13.168L11.4453 12.168C11.7521 11.9634 12.1467 11.9443 12.4718 12.1183Z"
                        fill="rgb(102, 102, 102)"
                    />
                </svg>
            </div>
                <ErrorMessage />
        </label>
);
}
