import {useCallback, useMemo, useRef, useState} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {UseAsyncSelectOptionReturn} from '@givewp/admin/types';

/**
 * Custom hook for handling async option selection with pagination and search
 *
 * @since TBD Work out the page from the options already loaded, so a remounted select starts at the first page.
 * @since TBD Show a picked option right away instead of the previous one while its record loads.
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
}: AsyncSelectOptionsConfig): UseAsyncSelectOptionReturn {
    const [error, setError] = useState<Error | null>(null);
    const loadedOptionsByValue = useRef(new Map<number, Option>());

    /* Until the record for a new selection arrives, the option picked from the menu stands in for it. */
    const selectedOption = useMemo<Option | null>(() => {
        if (!recordId) {
            return null;
        }

        const recordOption = selectedOptionRecord ? optionFormatter(selectedOptionRecord) : null;

        return Number(recordOption?.value) === Number(recordId)
            ? recordOption
            : loadedOptionsByValue.current.get(Number(recordId)) ?? null;
    }, [selectedOptionRecord, recordId]);

    // Load options function for AsyncPaginate
    const loadOptions = useCallback(async (searchInput: string, loadedOptions: Option[] = []) => {
        /* The select passes the options it holds for this search, and holds none after a remount or a new search. */
        const currentPage = Math.floor(loadedOptions.length / perPage) + 1;

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

            const newOptions: Option[] = (records || []).map(optionFormatter);

            newOptions.forEach((option) => loadedOptionsByValue.current.set(Number(option.value), option));

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
    }, [JSON.stringify(queryParams)]);

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

