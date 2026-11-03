import {viewFilterValue} from '../adapters/viewToParameters';
import type {ListTableParameters} from '../ListTablePage/types';
import type {ListTableView} from '../state/types';
import {dayBounds, parseDateToken, resolveDateRange} from './dateRange';
import type {DateFilterDeclaration, FilterDeclaration, FilterValue, OptionsFilterDeclaration} from './types';

/**
 * The value a declaration holds in the view, or undefined when it holds nothing.
 */
function appliedValue(view: ListTableView, id: string): FilterValue | undefined {
    const value = viewFilterValue(view, id);

    if (typeof value === 'string') {
        return value === '' ? undefined : value;
    }

    if (Array.isArray(value) && value.length > 0) {
        return value.map(String);
    }

    return undefined;
}

function dateParameters(declaration: DateFilterDeclaration, value: FilterValue, today: string): ListTableParameters {
    const range = typeof value === 'string' ? parseDateToken(value) : undefined;

    if (!range) {
        return {};
    }

    const {start, end} = dayBounds(resolveDateRange(range, today));

    return {
        ...(start && {[declaration.parameters.start]: start}),
        ...(end && {[declaration.parameters.end]: end}),
    };
}

function emptyOptionsParameters(declaration: OptionsFilterDeclaration): ListTableParameters {
    return declaration.whenEmpty ?? {};
}

/**
 * Builds the list request parameters from the filter declarations and the view. `today` is the
 * site's current day, fixed per request, and is what the date presets count from. Multiple values
 * stay lists; the query hook joins them. A filter without a value adds nothing.
 *
 * @since TBD
 */
export function filterRequestParameters(
    declarations: FilterDeclaration[],
    view: ListTableView,
    today: string
): ListTableParameters {
    return declarations.reduce<ListTableParameters>((parameters, declaration) => {
        if (declaration.kind === 'search') {
            return view.search ? {...parameters, [declaration.parameter]: view.search} : parameters;
        }

        const value = appliedValue(view, declaration.id);

        if (value === undefined) {
            return declaration.kind === 'options'
                ? {...parameters, ...emptyOptionsParameters(declaration)}
                : parameters;
        }

        if (declaration.kind === 'date') {
            return {...parameters, ...dateParameters(declaration, value, today)};
        }

        // "No campaign" is sent as the text `none`, not as a list.
        const isNone = Array.isArray(value) && value[0] === 'none';

        return {...parameters, [declaration.parameter]: isNone ? 'none' : value};
    }, {});
}
