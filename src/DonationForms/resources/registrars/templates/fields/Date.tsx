import DatePicker from 'react-datepicker';

import {DateProps} from '@givewp/forms/propTypes';
import 'react-datepicker/dist/react-datepicker.css';
import styles from '../styles.module.scss';
import {Controller} from 'react-hook-form';

export default function Date({Label, ErrorMessage, description, dateFormat, inputProps}: DateProps) {
    const {useFormContext} = window.givewp.form.hooks;
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {name} = inputProps;
    const {control} = useFormContext();

    dateFormat = dateFormat.replace('mm', 'MM');


    return (
        <label className={styles.dateField}>
            <Label />
            {description && <FieldDescription description={description} />}
            <Controller
                control={control}
                name={name}
                render={({
                    field: {onChange, onBlur, value, ref},
                }) =>
                        <DatePicker
                            ref={(element) => {
                                element && ref(element.input)
                            }}
                            dateFormat={dateFormat}
                            selected={value}
                            onChange={onChange}
                            onBlur={onBlur}
                        />
                }
            />

            <ErrorMessage />
        </label>
    );
}
