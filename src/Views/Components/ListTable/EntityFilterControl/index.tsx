import {Button, CheckboxControl, RadioControl, SearchControl, Spinner} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {applyExclusiveValues} from '../filters/exclusive';
import type {FilterOption} from '../filters/types';
import './styles.scss';

/**
 * @since TBD
 */
export interface EntityFilterControlProps {
    /** Names the control and the option list for assistive technology. */
    label: string;
    /** The options loaded so far. */
    options: FilterOption[];
    /** The chosen option values. */
    value: string[];
    /** Checkboxes when true, radio buttons when false. */
    multiple: boolean;
    /** An extra first choice such as "No campaign". It cannot be combined with other values. */
    noneOption?: FilterOption;
    /** The text in the search box. */
    search: string;
    onSearch: (search: string) => void;
    isLoading: boolean;
    /** A short message when the load failed. */
    error?: string;
    hasMore: boolean;
    onLoadMore: () => void;
    onRetry: () => void;
    onChange: (value: string[]) => void;
}

/**
 * The popover content of a filter whose options come from the server: a search box, a list of
 * options, and the loading, failure and "load more" states. It never fetches; the parent passes
 * the options and reacts to the callbacks.
 *
 * @since TBD
 */
export default function EntityFilterControl({
    label,
    options,
    value,
    multiple,
    noneOption,
    search,
    onSearch,
    isLoading,
    error,
    hasMore,
    onLoadMore,
    onRetry,
    onChange,
}: EntityFilterControlProps) {
    const choices = noneOption ? [noneOption, ...options] : options;
    const exclusive = noneOption ? [noneOption.value] : [];
    const isEmpty = options.length === 0 && !isLoading && !error;

    const toggle = (optionValue: string, checked: boolean) => {
        const next = checked ? [...value, optionValue] : value.filter((chosen) => chosen !== optionValue);

        onChange(applyExclusiveValues(value, next, exclusive));
    };

    return (
        <div className="givewp-entity-filter-control">
            <SearchControl
                label={label}
                value={search}
                onChange={onSearch}
                hideLabelFromVision={false}
                __nextHasNoMarginBottom
            />

            {isLoading && (
                <div role="status" className="givewp-entity-filter-control__status">
                    <Spinner />
                    <span>{__('Loading…', 'give')}</span>
                </div>
            )}

            {error && (
                <div role="alert" className="givewp-entity-filter-control__error">
                    <span>{error}</span>
                    <Button variant="secondary" onClick={onRetry}>
                        {__('Retry', 'give')}
                    </Button>
                </div>
            )}

            {isEmpty && <p className="givewp-entity-filter-control__empty">{__('No matches', 'give')}</p>}

            {choices.length > 0 && !multiple && (
                <div role="radiogroup" aria-label={label} className="givewp-entity-filter-control__options">
                    <RadioControl
                        selected={value[0] ?? ''}
                        options={choices}
                        onChange={(selected: string) => onChange([selected])}
                    />
                </div>
            )}

            {choices.length > 0 && multiple && (
                <div role="group" aria-label={label} className="givewp-entity-filter-control__options">
                    {choices.map((option) => (
                        <CheckboxControl
                            key={option.value}
                            className="givewp-entity-filter-control__option"
                            label={option.label}
                            checked={value.includes(option.value)}
                            onChange={(checked: boolean) => toggle(option.value, checked)}
                            __nextHasNoMarginBottom
                        />
                    ))}
                </div>
            )}

            {hasMore && !error && (
                <Button
                    variant="tertiary"
                    className="givewp-entity-filter-control__more"
                    disabled={isLoading}
                    onClick={onLoadMore}
                >
                    {__('Load more', 'give')}
                </Button>
            )}
        </div>
    );
}
