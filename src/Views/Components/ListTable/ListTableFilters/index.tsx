import {useEffect, useRef, useState} from 'react';
import {Button, DropdownMenu} from '@wordpress/components';
import type {Filter} from '@wordpress/dataviews';
import {__, sprintf} from '@wordpress/i18n';
import {closeSmall, plus} from '@wordpress/icons';
import {filterFieldId} from '../adapters/viewToParameters';
import DateRangeFilterControl from '../DateRangeFilterControl';
import FilterChip from '../FilterChip';
import {filterChipText} from '../filters/chipText';
import {dateToken, parseDateToken} from '../filters/dateRange';
import {applyExclusiveValues} from '../filters/exclusive';
import {formatSiteDate} from '../filters/siteDate';
import type {
    BarFilterDeclaration,
    FilterOption,
    FilterValue,
    OptionsFilterDeclaration,
    StatusVocabularyEntry,
} from '../filters/types';
import OptionsFilterControl from '../OptionsFilterControl';
import type {ListTableScreenKey, ListTableView} from '../state/types';
import EntityFilter from './EntityFilter';
import './styles.scss';

/**
 * @since TBD
 */
export interface ListTableFiltersProps {
    /** The entity's bar filters, in declaration order. */
    declarations: BarFilterDeclaration[];
    view: ListTableView;
    onChangeView: (view: ListTableView) => void;
    /** The server's status vocabulary, for `options: 'statuses'`. */
    statuses: StatusVocabularyEntry[];
    /** Sent with option requests, so the server checks the screen's permission. */
    screen?: ListTableScreenKey;
    /** The test-mode chip. It is not a declaration; the header switch owns it. */
    testMode?: {label: string; isOn: boolean; onChange: (isOn: boolean) => void};
    canReset: boolean;
    onReset: () => void;
}

/** What gets focus after a chip is removed: "Add filter", or the chip of a primary filter. */
type FocusTarget = {addFilter: true} | {chip: string};

/**
 * The values a filter holds in the view, as a list. Empty when it holds nothing.
 */
function valuesOf(value: unknown): string[] {
    if (value === undefined || value === null || value === '') {
        return [];
    }

    return ([] as unknown[]).concat(value).map(String);
}

/**
 * The choices of an options filter: the filterable vocabulary entries in their order, or the fixed list.
 */
function optionsOf(declaration: OptionsFilterDeclaration, statuses: StatusVocabularyEntry[]): FilterOption[] {
    if (declaration.options !== 'statuses') {
        return declaration.options;
    }

    return statuses.filter((entry) => entry.filterable).map(({value, label}) => ({value, label}));
}

/**
 * The label of an options value. A status the filter does not offer is still named from the vocabulary.
 */
function optionLabel(declaration: OptionsFilterDeclaration, statuses: StatusVocabularyEntry[], value: string): string {
    const all: FilterOption[] = declaration.options === 'statuses' ? statuses : declaration.options;

    return all.find((option) => option.value === value)?.label ?? '';
}

const isMultiple = (declaration: BarFilterDeclaration) => declaration.kind !== 'date' && declaration.multiple;

/**
 * The filter bar of a list: the primary filters as chips that are always there, the secondary
 * filters the admin added, an "Add filter" menu for the rest, the test-mode chip and "Reset". It
 * reads the entity's declaration and keeps every value in the view's filters (`filter-<id>`), so
 * the URL, reset and the empty state work from the same place. It has no screen-specific branches.
 *
 * @since TBD
 */
export default function ListTableFilters({
    declarations,
    view,
    onChangeView,
    statuses,
    screen,
    testMode,
    canReset,
    onReset,
}: ListTableFiltersProps) {
    const [justAdded, setJustAdded] = useState<string>();
    const addFilterRef = useRef<HTMLDivElement>(null);
    const chipRefs = useRef(new Map<string, HTMLDivElement>());
    const pendingFocus = useRef<FocusTarget>();

    // A removed chip took the focus with it, so it moves once the bar has re-rendered.
    useEffect(() => {
        const target = pendingFocus.current;

        if (!target) {
            return;
        }

        pendingFocus.current = undefined;
        const holder = 'chip' in target ? chipRefs.current.get(target.chip) : addFilterRef.current;
        holder?.querySelector<HTMLButtonElement>('button')?.focus();
    });

    const filters = view.filters ?? [];
    const entryOf = (id: string) => filters.find((filter) => filter.field === filterFieldId(id));
    const primary = declarations.filter((declaration) => declaration.isPrimary);
    const added = declarations.filter((declaration) => !declaration.isPrimary && entryOf(declaration.id));
    const available = declarations.filter((declaration) => !declaration.isPrimary && !entryOf(declaration.id));
    const shown = [...primary, ...added];

    if (shown.length === 0 && available.length === 0 && !testMode?.isOn && !canReset) {
        return null;
    }

    /**
     * The view with the filter set to `value`. A secondary filter keeps an empty entry, so its chip
     * stays in the bar; a primary one drops it, because its chip is always there.
     */
    const withValue = (declaration: BarFilterDeclaration, value: FilterValue | undefined): ListTableView => {
        const field = filterFieldId(declaration.id);
        const isEmpty = value === undefined || value.length === 0;
        const others = filters.filter((filter) => filter.field !== field);

        if (isEmpty && declaration.isPrimary) {
            return {...view, filters: others};
        }

        const next: Filter = {
            field,
            operator: isMultiple(declaration) ? 'isAny' : 'is',
            value: isEmpty ? undefined : value,
        };
        const exists = others.length !== filters.length;

        return {
            ...view,
            filters: exists ? filters.map((filter) => (filter.field === field ? next : filter)) : [...filters, next],
        };
    };

    const setValues = (declaration: BarFilterDeclaration, values: string[]) =>
        onChangeView(withValue(declaration, isMultiple(declaration) ? values : values[0]));

    const add = (declaration: BarFilterDeclaration) => {
        setJustAdded(declaration.id);
        onChangeView(withValue(declaration, undefined));
    };

    const remove = (declaration: BarFilterDeclaration) => {
        pendingFocus.current = declaration.isPrimary ? {chip: declaration.id} : {addFilter: true};
        onChangeView({...view, filters: filters.filter((filter) => filter.field !== filterFieldId(declaration.id))});
    };

    const renderFilter = (declaration: BarFilterDeclaration) => {
        const values = valuesOf(entryOf(declaration.id)?.value);
        const hasValue = values.length > 0;
        const chipValue = hasValue ? (isMultiple(declaration) ? values : values[0]) : undefined;
        const chip = {
            canRemove: !declaration.isPrimary || hasValue,
            removeLabel: declaration.isPrimary
                ? /* translators: %s: filter name such as "Status". */
                  sprintf(__('Reset %s', 'give'), declaration.label)
                : /* translators: %s: filter name such as "Donation date". */
                  sprintf(__('Remove %s', 'give'), declaration.label),
            defaultOpen: justAdded === declaration.id,
            onRemove: () => remove(declaration),
        };

        switch (declaration.kind) {
            case 'options': {
                const {text, fullText} = filterChipText(declaration, chipValue, {
                    labelOf: (value) => optionLabel(declaration, statuses, value),
                    formatDate: formatSiteDate,
                });

                return (
                    <FilterChip {...chip} text={text} fullText={fullText} hasValue={hasValue}>
                        {() => (
                            <OptionsFilterControl
                                label={declaration.label}
                                options={optionsOf(declaration, statuses)}
                                value={values}
                                multiple={declaration.multiple}
                                onChange={(next) =>
                                    setValues(
                                        declaration,
                                        applyExclusiveValues(values, next, declaration.exclusiveValues ?? [])
                                    )
                                }
                            />
                        )}
                    </FilterChip>
                );
            }
            case 'entity':
                return (
                    <EntityFilter
                        {...chip}
                        declaration={declaration}
                        value={values}
                        screen={screen}
                        onChange={(next) => setValues(declaration, next)}
                    />
                );
            case 'date': {
                const {text, fullText} = filterChipText(declaration, chipValue, {
                    labelOf: () => '',
                    formatDate: formatSiteDate,
                });

                return (
                    <FilterChip {...chip} text={text} fullText={fullText} hasValue={hasValue}>
                        {(close) => (
                            <DateRangeFilterControl
                                label={declaration.label}
                                value={hasValue ? parseDateToken(values[0]) : undefined}
                                formatDate={formatSiteDate}
                                onChange={(range) => {
                                    setValues(declaration, range ? [dateToken(range)] : []);
                                    close();
                                }}
                            />
                        )}
                    </FilterChip>
                );
            }
        }
    };

    return (
        <div className="givewp-list-table-filters" role="group" aria-label={__('Filters', 'give')}>
            {shown.map((declaration) => (
                <div
                    key={declaration.id}
                    className="givewp-list-table-filters__item"
                    ref={(element) => {
                        element
                            ? chipRefs.current.set(declaration.id, element)
                            : chipRefs.current.delete(declaration.id);
                    }}
                >
                    {renderFilter(declaration)}
                </div>
            ))}

            {testMode?.isOn && (
                <div className="givewp-filter-chip givewp-filter-chip--has-value givewp-list-table-filters__test-mode">
                    <span className="givewp-list-table-filters__test-mode-text">{testMode.label}</span>
                    <Button
                        className="givewp-filter-chip__remove"
                        icon={closeSmall}
                        /* translators: %s: the test-mode chip text, "Test mode". */
                        label={sprintf(__('Remove %s', 'give'), testMode.label)}
                        onClick={() => testMode.onChange(false)}
                    />
                </div>
            )}

            {available.length > 0 && (
                <div ref={addFilterRef} className="givewp-list-table-filters__add">
                    <DropdownMenu
                        icon={plus}
                        label={__('Add filter', 'give')}
                        text={__('Add filter', 'give')}
                        controls={available.map((declaration) => ({
                            title: declaration.label,
                            onClick: () => add(declaration),
                        }))}
                    />
                </div>
            )}

            {canReset && (
                <Button variant="tertiary" className="givewp-list-table-filters__reset" onClick={onReset}>
                    {__('Reset', 'give')}
                </Button>
            )}
        </div>
    );
}
