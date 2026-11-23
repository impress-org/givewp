import {useEffect, useState, type ChangeEvent, type KeyboardEvent} from 'react';
import {Button, __experimentalHStack as HStack} from '@wordpress/components';
import {DataViews} from '@wordpress/dataviews';
import type {View} from '@wordpress/dataviews';
import {createInterpolateElement} from '@wordpress/element';
import {__, _x, isRTL, sprintf} from '@wordpress/i18n';
import {next, previous} from '@wordpress/icons';

/**
 * Up to this many pages the stock page select is fine. Above it the select has too many options: the
 * stock one builds an <option> per page, so 1,000,000 rows at 30 per page is 33,334 of them, and the
 * page freezes while the browser builds and lays them out.
 */
export const MANY_PAGES = 100;

// The stock page buttons are `compact`. The bundled `@wordpress/components` types are older than the
// WordPress runtime that provides the component, and do not list `size`, so it goes in as a spread.
const COMPACT = {size: 'compact'} as Record<string, unknown>;

interface PaginationProps {
    view: View;
    onChangeView: (view: View) => void;
    totalPages: number;
}

/**
 * The stock DataViews page control, with a number box in place of the page select once there are many
 * pages. Same markup, classes and strings (core's text domain) as the stock control, so it looks and reads
 * the same. The buttons use `aria-disabled` at the ends, so they keep the focus and need no `@wordpress/components` version that knows `accessibleWhenDisabled`. The page changes when the box is committed (Enter or leaving it), not on every key, so typing
 * "250" does not load pages 2 and 25 on the way.
 *
 * @since TBD
 */
export function LargePagination({view, onChangeView, totalPages}: PaginationProps) {
    const currentPage = view.page ?? 1;
    const [draft, setDraft] = useState(String(currentPage));

    // The page can also change from outside (the buttons, a filter reset): follow it.
    useEffect(() => setDraft(String(currentPage)), [currentPage]);

    const goTo = (page: number) => onChangeView({...view, page});

    const commit = () => {
        const typed = Math.trunc(Number(draft));

        if (!Number.isFinite(typed) || draft.trim() === '') {
            setDraft(String(currentPage));
            return;
        }

        const page = Math.min(Math.max(typed, 1), totalPages);
        setDraft(String(page));

        if (page !== currentPage) {
            goTo(page);
        }
    };

    return (
        <HStack expanded={false} className="dataviews-pagination" justify="end" spacing={6}>
            <HStack justify="flex-start" expanded={false} spacing={1} className="dataviews-pagination__page-select">
                {createInterpolateElement(
                    sprintf(
                        // translators: 1: Current page number, 2: Total number of pages.
                        _x('<div>Page</div>%1$s<div>of %2$d</div>', 'paging'),
                        '<CurrentPage />',
                        totalPages
                    ),
                    {
                        div: <div aria-hidden />,
                        CurrentPage: (
                            <input
                                className="givewp-list-table__page-input"
                                type="number"
                                inputMode="numeric"
                                min={1}
                                max={totalPages}
                                aria-label={__('Current page')}
                                value={draft}
                                style={{inlineSize: `calc(${String(totalPages).length}ch + 1.5rem)`}}
                                onChange={(event: ChangeEvent<HTMLInputElement>) => setDraft(event.target.value)}
                                onBlur={commit}
                                onKeyDown={(event: KeyboardEvent<HTMLInputElement>) => {
                                    if (event.key === 'Enter') {
                                        event.preventDefault();
                                        commit();
                                    }
                                }}
                            />
                        ),
                    }
                )}
            </HStack>
            <HStack expanded={false} spacing={1}>
                <Button
                    onClick={() => currentPage > 1 && goTo(currentPage - 1)}
                    aria-disabled={currentPage === 1}
                    label={__('Previous page')}
                    icon={isRTL() ? next : previous}
                    showTooltip
                    {...COMPACT}
                    tooltipPosition="top"
                />
                <Button
                    onClick={() => currentPage < totalPages && goTo(currentPage + 1)}
                    aria-disabled={currentPage >= totalPages}
                    label={__('Next page')}
                    icon={isRTL() ? previous : next}
                    showTooltip
                    {...COMPACT}
                    tooltipPosition="top"
                />
            </HStack>
        </HStack>
    );
}

/**
 * The page control while the total is not known yet: only the previous and next arrows, in the stock
 * control's markup. Previous works above page 1; Next is on while `hasNextPage` is true. No page box and no
 * "of N", because there is no N.
 *
 * @since TBD
 */
export function ArrowsPagination({
    view,
    onChangeView,
    hasNextPage,
}: {
    view: View;
    onChangeView: (view: View) => void;
    hasNextPage: boolean;
}) {
    const currentPage = view.page ?? 1;
    const goTo = (page: number) => onChangeView({...view, page});

    return (
        <HStack expanded={false} className="dataviews-pagination givewp-list-table__arrows" justify="end" spacing={6}>
            <HStack expanded={false} spacing={1}>
                <Button
                    onClick={() => currentPage > 1 && goTo(currentPage - 1)}
                    aria-disabled={currentPage === 1}
                    label={__('Previous page')}
                    icon={isRTL() ? next : previous}
                    showTooltip
                    {...COMPACT}
                    tooltipPosition="top"
                />
                <Button
                    onClick={() => hasNextPage && goTo(currentPage + 1)}
                    aria-disabled={!hasNextPage}
                    label={__('Next page')}
                    icon={isRTL() ? previous : next}
                    showTooltip
                    {...COMPACT}
                    tooltipPosition="top"
                />
            </HStack>
        </HStack>
    );
}

/**
 * The page control for the footer: the stock one for a normal number of pages, the number box above
 * `MANY_PAGES`. The stock control hides itself when there is one page, and so does this one.
 *
 * @since TBD
 */
export default function ListTablePagination(props: PaginationProps) {
    if (props.totalPages > MANY_PAGES && !props.view.infiniteScrollEnabled) {
        return <LargePagination {...props} />;
    }

    return <DataViews.Pagination />;
}
