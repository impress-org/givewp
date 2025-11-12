/**
 * External dependencies
 */
import {useFormContext, useFormState} from 'react-hook-form';

/**
 * Internal dependencies
 */
import {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import {formatToDateTimeLocalInput, convertLocalDateTimeToISOString} from '@givewp/admin/common';

/**
 *
 * DateTimeLocalField component that formats the date and time to a datetime-local input and is compatible with the server-side validation date-time format RFC3339 validation.
 *
 * @since 4.13.0
 */
export default function DateTimeLocalField({name, label}: {name: string, label: string}) {
    const {watch, setValue} = useFormContext();
    const {errors} = useFormState();
    const value = watch(name);

    return (
        <AdminSectionField error={errors[name]?.message as string}>
            <label htmlFor={name}>{label}</label>
            <input
                type="datetime-local"
                id={name}
                value={formatToDateTimeLocalInput(value)}
                onChange={(e) => {
                    setValue(name, convertLocalDateTimeToISOString(e.target.value), {
                        shouldDirty: true,
                    });
                }}
            />
        </AdminSectionField>
    );
}
