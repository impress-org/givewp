import {Button, Calendar, CalendarCell, CalendarGrid, DatePicker, Heading, Label as AriaLabel, Popover, Group, DateInput, DateSegment, Dialog} from 'react-aria-components';
import { useRef } from 'react';
import {format, parse} from 'date-fns';
import {CalendarDate} from '@internationalized/date';

import {DateProps} from '@givewp/forms/propTypes';
import 'react-datepicker/dist/react-datepicker.css';
import styles from './styles.module.scss';

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

    const dateFormatNormalized = dateFormat.replace('mm', 'MM');
    const parsedValue = value ? parse(value, dateFormatNormalized, new window.Date()) : null;
    const calendarDate = parsedValue ? new CalendarDate(parsedValue.getFullYear(), parsedValue.getMonth() + 1, parsedValue.getDate()) : null;

    const handleDateChange = (date) => {
        const formattedDate = format(new window.Date(date.year, date.month - 1, date.day), dateFormatNormalized);
        setValue(inputProps.name, formattedDate);
    };

    return (
        <DatePicker
            id={`givewp-date-picker__${inputProps.name}`}
            aria-invalid={fieldError ? 'true' : 'false'}
            className={styles.dateField}
            onChange={handleDateChange}
            value={calendarDate}
            shouldForceLeadingZeros={true}
        >
            <AriaLabel>
                <Label />
                {description && <FieldDescription description={description} />}
            </AriaLabel>

            <Group className={styles.dateInputContainer}>
                <DateInput className={styles.dateInput} data-dateformat={dateFormat.replaceAll('/', '').toLowerCase()}>
                    {(segment) => <DateSegment segment={segment} />}
                </DateInput>
                <Button className={styles.dateInputButton}>▼</Button>
            </Group>
            <Popover placement="bottom" UNSTABLE_portalContainer={document.getElementById(`givewp-date-picker__${inputProps.name}`)}>
                <Dialog>
                    <Calendar>
                        <header>
                            <Button slot="previous">◀</Button>
                            <Heading />
                            <Button slot="next">▶</Button>
                        </header>
                        <CalendarGrid>
                            {(date) => <CalendarCell date={date} />}
                        </CalendarGrid>
                    </Calendar>
                </Dialog>
            </Popover>

            <ErrorMessage />
        </DatePicker>
    );
}
