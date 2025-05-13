import { useCallback, useMemo } from "react";
import { format, parse } from "date-fns";
import { CalendarDate } from "@internationalized/date";

function useDateHandling(dateFormat: string, inputName: string) {
    const {useFormContext, useWatch} = window.givewp.form.hooks;
    const {setValue} = useFormContext();
    const value = useWatch({name: inputName});

    const dateFormatNormalized = useMemo(() => dateFormat.replace('mm', 'MM'), [dateFormat]);

    const parsedValue = useMemo(() => {
        if (!value) return null;
        try {
            return parse(value, dateFormatNormalized, new window.Date());
        } catch {
            return null;
        }
    }, [value, dateFormatNormalized]);

    const calendarDate = useMemo(() => {
        if (!parsedValue) return null;
        return new CalendarDate(
            parsedValue.getFullYear(),
            parsedValue.getMonth() + 1,
            parsedValue.getDate()
        );
    }, [parsedValue]);

    const handleDateChange = useCallback((date: CalendarDate) => {
        const formattedDate = format(
            new window.Date(date.year, date.month - 1, date.day),
            dateFormatNormalized
        );
        setValue(inputName, formattedDate);
    }, [dateFormatNormalized, inputName, setValue]);

    return {
        calendarDate,
        handleDateChange
    };
};

export default useDateHandling;
