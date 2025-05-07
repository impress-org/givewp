import {Button, Calendar, CalendarCell, CalendarGrid, DatePicker, Heading, Label as AriaLabel, Popover, Group, DateInput, DateSegment, Dialog} from 'react-aria-components';
import useDateHandling from './useDateHandlingHook';
import {UseFormRegisterReturn} from 'react-hook-form';

import {DateProps} from '@givewp/forms/propTypes';
import 'react-datepicker/dist/react-datepicker.css';
import styles from './styles.module.scss';

interface DateFieldProps extends Omit<DateProps, 'dateFormat' | 'inputProps'> {
    dateFormat: string;
    fieldError: string;
    inputProps: Pick<UseFormRegisterReturn, 'name'>;
}

/**
 * @unreleased
 */
export default function Date({
    Label,
    ErrorMessage,
    description,
    dateFormat = 'yyyy/mm/dd',
    fieldError,
    inputProps,
}: DateFieldProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {calendarDate, handleDateChange} = useDateHandling(dateFormat, inputProps.name);

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
                <DateInput
                    className={styles.dateInput}
                    data-dateformat={dateFormat.replaceAll('/', '').toLowerCase()}
                >
                    {(segment) => <DateSegment segment={segment} />}
                </DateInput>
                <Button className={styles.dateInputButton}>▼</Button>
            </Group>

            <Popover
                placement="bottom"
                UNSTABLE_portalContainer={document.getElementById(`givewp-date-picker__${inputProps.name}`)}
            >
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
