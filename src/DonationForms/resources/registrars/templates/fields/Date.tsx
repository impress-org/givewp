import DatePicker from 'react-datepicker';
import {format, parse} from 'date-fns';

import {DateProps} from '@givewp/forms/propTypes';
import 'react-datepicker/dist/react-datepicker.css';
import styles from '../styles.module.scss';
import {useEffect, useRef} from "react";

/**
 * @since 4.3.2 manually focus the visible input when error is present.
 * @since 4.3.0 add aria-required attribute.
 */
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

    const ref = useRef(null);

    dateFormat = dateFormat.replace('mm', 'MM');

    useEffect(() => {
        if (fieldError && ref.current) {
            ref.current.focus();
        }
    }, [fieldError]);

    return (
        <label ref={ref} className={styles.dateField}>
            <Label />
            {description && <FieldDescription description={description} />}
            <input type="hidden" {...inputProps} />
            <DatePicker
                id={inputProps.name}
                ariaInvalid={fieldError ? 'true' : 'false'}
                dateFormat={dateFormat}
                selected={value ? parse(value, dateFormat, new window.Date()) : null}
                onChange={(date) => setValue(inputProps.name, date ? format(date, dateFormat) : '')}
                ariaRequired={inputProps['aria-required']}
            />

            <ErrorMessage />
        </label>
    );
}
