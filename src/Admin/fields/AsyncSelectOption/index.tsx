import { __ } from '@wordpress/i18n';
import { AsyncPaginate } from 'react-select-async-paginate';
import { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { SelectOption } from '@givewp/admin/types';

import styles from './styles.module.scss';


/**
 * @since 4.11.0
 */
export default function AsyncSelectOption({
    name,
    label,
    description = '',
    selectedOption,
    loadOptions,
    mapOptionsForMenu,
    handleChange,
    isLoadingError,
    errorMessage,
    searchPlaceholder,
    loadingMessage,
    loadingError,
    ariaLabel,
    noOptionsMessage,
    children
}: AsyncSelectOptionProps) {
    return (
        <AdminSectionField error={errorMessage}>
            <label htmlFor={name}>{label}</label>
            {description && <p>{description}</p>}
            {isLoadingError ? (
                <div role="alert" style={{ color: 'var(--givewp-red-500)', fontSize: '0.875rem' }}>
                    {loadingError}
                </div>
            ) : (
                <AsyncPaginate
                    inputId={name}
                    className={styles.searchableSelect}
                    classNamePrefix="searchableSelect"
                    value={selectedOption}
                    loadOptions={loadOptions}
                    mapOptionsForMenu={mapOptionsForMenu}
                    onChange={handleChange}
                    debounceTimeout={600}
                    placeholder={searchPlaceholder}
                    loadingMessage={() => loadingMessage}
                    noOptionsMessage={() => noOptionsMessage}
                    aria-label={ariaLabel}
                />
            )}
            {children}
        </AdminSectionField>
    );
}

interface AsyncSelectOptionProps {
    name: string;
    label: string;
    description?: string;
    handleChange: (selectedOption: SelectOption) => void;
    selectedOption: SelectOption | null;
    loadOptions: (searchInput: string) => Promise<{
        options: SelectOption[];
        hasMore: boolean;
    }>;
    mapOptionsForMenu: (options: SelectOption[]) => SelectOption[];
    isLoadingError: Error | null;
    errorMessage: string;
    searchPlaceholder: string;
    loadingMessage: string;
    loadingError: string,
    noOptionsMessage: string;
    ariaLabel: string;
    children?: JSX.Element | JSX.Element[];
}
