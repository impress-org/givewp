import {useCallback, useEffect, useState} from 'react';
import {useEntityRecord} from '@wordpress/core-data';
import apiFetch from '@wordpress/api-fetch';

/**
 * Custom hook for handling async option selection with pagination and search
 *
 * @unreleased
 */
export function useAsyncSelectOptions({
    recordId,
    entity,
    endpoint,
    optionFormatter,
    queryParams,
    perPage = 30,
}: AsyncSelectOptionsConfig) {
    const [page, setPage] = useState(0);
    const [selectedOption, setSelectedOption] = useState<Option | null>(null);
    const [error, setError] = useState<Error | null>(null);

    const {
        record,
        hasResolved,
    } = useEntityRecord<string[]>('givewp', entity, recordId);

    useEffect(() => {
        if (hasResolved && record && recordId) {
            setSelectedOption(optionFormatter(record));
        } else if (!recordId) {
            setSelectedOption(null);
        }
    }, [record, hasResolved, recordId]);

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
            const records = await apiFetch<[]>({
                path: `${endpoint}?${params.toString()}`,
            });

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
            const loadError = err instanceof Error ? err : new Error(`Failed to load ${entity}`);
            setError(loadError);
            console.error(`Failed to load ${entity}`, loadError);

            return {
                options: [],
                hasMore: false,
            };
        }
    }, [page]);

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
    entity: string;
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

