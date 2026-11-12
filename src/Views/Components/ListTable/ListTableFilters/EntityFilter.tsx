import {__} from '@wordpress/i18n';
import EntityFilterControl from '../EntityFilterControl';
import FilterChip from '../FilterChip';
import {filterChipText} from '../filters/chipText';
import {NONE_VALUE} from '../filters/filterKinds';
import {MAX_FILTER_VALUES} from '../filters/filterValue';
import {formatSiteDate} from '../filters/siteDate';
import type {EntityFilterDeclaration, FilterOption} from '../filters/types';
import useFilterOptionLabels from '../hooks/useFilterOptionLabels';
import useFilterOptions from '../hooks/useFilterOptions';
import type {ListTableScreenKey} from '../state/types';

/**
 * The value of the exclusive "No campaign" choice.
 *
 * @since TBD
 */
export {NONE_VALUE} from '../filters/filterKinds';

const noneOption = (): FilterOption => ({value: NONE_VALUE, label: __('No campaign', 'give')});

/**
 * @since TBD
 */
export interface EntityFilterProps {
    declaration: EntityFilterDeclaration;
    /** The chosen ids, or `['none']`. */
    value: string[];
    screen?: ListTableScreenKey;
    canRemove: boolean;
    removeLabel: string;
    defaultOpen: boolean;
    onRemove: () => void;
    onChange: (value: string[]) => void;
}

/**
 * The popover content. It mounts only while the popover is open, so the options load only then.
 */
function EntityFilterPopover({
    declaration,
    value,
    screen,
    onChange,
}: Pick<EntityFilterProps, 'declaration' | 'value' | 'screen' | 'onChange'>) {
    const {options, search, setSearch, isLoading, error, hasMore, loadMore, retry} = useFilterOptions({
        source: declaration.source,
        screen,
        enabled: true,
    });

    return (
        <EntityFilterControl
            label={declaration.label}
            options={options}
            value={value}
            multiple={declaration.multiple}
            noneOption={declaration.allowNone ? noneOption() : undefined}
            search={search}
            onSearch={setSearch}
            isLoading={isLoading}
            // The server's own message can name tables or fields, so the admin sees a fixed one.
            error={error ? __('Could not load the options. Try again.', 'give') : undefined}
            hasMore={hasMore}
            onLoadMore={loadMore}
            onRetry={retry}
            // The address keeps 50 values at most, so the list must not filter by more than the address holds.
            onChange={(next) => onChange(next.slice(0, MAX_FILTER_VALUES))}
        />
    );
}

/**
 * One entity filter (campaign, form, donor) in the bar: the chip, with names for the chosen ids,
 * and the searchable option list in its popover.
 *
 * @since TBD
 */
export default function EntityFilter({
    declaration,
    value,
    screen,
    canRemove,
    removeLabel,
    defaultOpen,
    onRemove,
    onChange,
}: EntityFilterProps) {
    const knownLabel = useFilterOptionLabels(declaration.source, screen, value);
    const labelOf = (item: string) => (item === NONE_VALUE ? noneOption().label : knownLabel(item) ?? '');
    const chipValue = declaration.multiple ? value : value[0];
    const {text, accessibleName} = filterChipText(declaration, value.length > 0 ? chipValue : undefined, {
        labelOf,
        formatDate: formatSiteDate,
    });

    return (
        <FilterChip
            text={text}
            accessibleName={accessibleName}
            hasValue={value.length > 0}
            canRemove={canRemove}
            removeLabel={removeLabel}
            defaultOpen={defaultOpen}
            onRemove={onRemove}
        >
            {() => <EntityFilterPopover declaration={declaration} value={value} screen={screen} onChange={onChange} />}
        </FilterChip>
    );
}
