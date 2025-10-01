/**
 * External dependencies
 */
import { Controller } from 'react-hook-form';
import ReactSelect from 'react-select';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import styles from './styles.module.scss';
import { SelectFieldProps } from './types';

/**
 * @unreleased
 */
export default function SelectField({
    name,
    label,
    placeholder,
    options,
    control,
    error,
    isDisabled = false,
}: SelectFieldProps) {
    return (
        <AdminSectionField error={error?.message as string}>
            <label htmlFor={name}>{__(label, 'give')}</label>
            <Controller
                name={name}
                control={control}
                rules={{ required: true }}
                render={({ field }) => (
                    <ReactSelect
                        {...field}
                        inputId={name}
                        options={options}
                        value={options.find(option => option.value === field.value) || null}
                        onChange={(selectedOption) => field.onChange(selectedOption?.value || null)}
                        placeholder={__(placeholder, 'give')}
                        isSearchable
                        isDisabled={isDisabled}
                        className={styles.searchableSelect}
                        classNamePrefix="searchableSelect"
                    />
                )}
            />
        </AdminSectionField>
    );
}
