/**
 * External dependencies
 */
import {useFormContext, useFormState} from 'react-hook-form';
import {formatToDateTimeLocalInput, convertLocalDateTimeToISOString} from '@givewp/admin/common';

/**
 * WordPress dependencies
 */
import {__} from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';

/**
 *
 * DateTimeLocalField component that formats the date and time to a datetime-local input and is compatible with the server-side validation date-time format RFC3339 validation.
 *
 * @unreleased
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
