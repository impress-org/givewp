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
            <input type="hidden" {...inputProps} />
            <DatePicker
                ariaInvalid={fieldError ? 'true' : 'false'}
                dateFormat={dateFormat}
                selected={value ? parse(value, dateFormat, new window.Date()) : null}
                onChange={(date) => setValue(inputProps.name, date ? format(date, dateFormat) : '')}
            />

            <ErrorMessage />
        </label>
    );
}
