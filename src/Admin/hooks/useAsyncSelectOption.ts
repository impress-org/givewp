import {useCallback, useEffect, useState} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {UseAsyncSelectOptionReturn} from '@givewp/admin/types';

/**
 * Custom hook for handling async option selection with pagination and search
 *
 * @since 4.11.0
 */
export function useAsyncSelectOptions({
    recordId,
    selectedOptionRecord,
    endpoint,
    recordsFormatter = (records: any) => records,
    optionFormatter,
    queryParams,
    perPage = 30,
    resetOnChange = false,
}: AsyncSelectOptionsConfig): UseAsyncSelectOptionReturn {
    const [page, setPage] = useState(0);
    const [selectedOption, setSelectedOption] = useState<Option | null>(null);
    const [error, setError] = useState<Error | null>(null);

    // Reset page when reset property changes
    useEffect(() => {
        if (resetOnChange !== undefined) {
            setPage(0);
        }
    }, [resetOnChange]);

    useEffect(() => {
        if (selectedOptionRecord && recordId) {
            setSelectedOption(optionFormatter(selectedOptionRecord));
        } else if (!recordId) {
            setSelectedOption(null);
        }
    }, [selectedOptionRecord, recordId]);

    // Load options function for AsyncPaginate
    const loadOptions = useCallback(async (searchInput: string) => {
        const currentPage = searchInput ? 1 : page + 1;

        const params = new URLSearchParams({
            ...queryParams,
            per_page: perPage.toString(),
            page: currentPage.toString(),
            ...(searchInput && {search: searchInput}),
        });

        setError(null);

        try {
            const records = recordsFormatter(await apiFetch<[]>({
                path: `${endpoint}?${params.toString()}`,
            }));

            const newOptions = (records || []).map(optionFormatter);

            // Update page state
            if (searchInput !== '') {
                setPage(1);
            } else if (!searchInput) {
                setPage(currentPage);
            }

            const hasMoreResults = (records?.length || 0) >= perPage;

            return {
                options: newOptions,
                hasMore: hasMoreResults,
            };
        } catch (err) {
            const loadError = err instanceof Error ? err : new Error(`Failed to load options`);
            setError(loadError);
            console.error(`Failed to load options`, loadError);

            return {
                options: [],
                hasMore: false,
            };
        }
    }, [page, JSON.stringify(queryParams)]);

    // Map options for menu (deduplication and ordering)
    const mapOptionsForMenu = useCallback(
        (options: Option[]) => filterOptionsForSelect(options, selectedOption),
        [selectedOption],
    );

    return {
        selectedOption,
        loadOptions,
        mapOptionsForMenu,
        error,
    };
}

export type Option = {
    value: number;
    label: string;
}

export type AsyncSelectOptionsConfig = {
    recordId: number | null;
    selectedOptionRecord: any;
    recordsFormatter?: (records: any) => any;
    optionFormatter: (record: any) => Option;
    endpoint: string;
    queryParams: {};
    perPage?: number;
    resetOnChange?: any;
}

export function filterOptionsForSelect(options: Option[], selectedOption: Option | null): Option[] {
    // Remove duplicates and sort alphabetically
    const filteredOptions = options
        .filter((option, index, self) => index === self.findIndex((t) => t.value === option.value))
        .sort((a, b) => a.label.localeCompare(b.label));

    // If no selected option, return filtered list
    if (!selectedOption) {
        return filteredOptions;
    }

    // Put selected option first, then other options (excluding the selected one)
    return [
        selectedOption,
        ...filteredOptions.filter(option => option.value !== selectedOption.value),
    ];
}

