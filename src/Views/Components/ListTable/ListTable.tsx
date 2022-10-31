import {useEffect, useRef, useState} from 'react';
import {__, _n, sprintf} from '@wordpress/i18n';
import cx from 'classnames';

import styles from './ListTable.module.scss';
import {Spinner} from '../index';
import {BulkActionCheckboxAll} from '@givewp/components/ListTable/BulkActionCheckbox';
import TableSort from '@givewp/components/ListTable/TableSort';
import ListTableRows from '@givewp/components/ListTable/ListTableRows';

export interface ListTableProps {
    //required
    table: {columns: Array<ListTableColumn>};
    title: string;
    data: {items: Array<{}>};

    //optional
    pluralName?: string;
    singleName?: string;
    rowActions?: (({item, data, addRow, removeRow}) => JSX.Element) | JSX.Element | JSX.Element[] | Function | null;
    parameters?: {};
    error?: {} | Boolean;
    isLoading?: Boolean;
    align?: 'start' | 'center' | 'end';
}

export interface ListTableColumn {
    //required
    name: string;
    text: string;
    isSortable: boolean;

    //optional
    inlineSize?: string;
    preset?: string;
    heading?: boolean;
    alignColumn?: 'start' | 'center' | 'end';
    addClass?: string;
    render?: ((item: {}) => JSX.Element) | JSX.Element | JSX.Element[] | null;
}

export const ListTable = ({
    table,
    singleName = __('item', 'give'),
    pluralName = __('items', 'give'),
    title,
    data,
    rowActions = null,
    parameters = {},
    error = false,
    isLoading = false,
    align = 'start',
}: ListTableProps) => {
    const [updateErrors, setUpdateErrors] = useState<{errors: Array<number>; successes: Array<number>}>({
        errors: [],
        successes: [],
    });
    const [errorOverlay, setErrorOverlay] = useState<string | boolean>(false);
    const [initialLoad, setInitialLoad] = useState<boolean>(true);
    const [loadingOverlay, setLoadingOverlay] = useState<string | boolean>(false);
    const [overlayWidth, setOverlayWidth] = useState(0);
    const [sortedData, setSortedData] = useState<Array<object>>([{}]);

    const [sort, setSort] = useState<{sortColumn: string; sortDirection: string}>({
        sortColumn: 'id',
        sortDirection: 'desc',
    });

    const tableRef = useRef<null | HTMLTableElement>();
    const isEmpty = !error && data?.items.length === 0;
    const {sortColumn, sortDirection} = sort;
    const {columns} = table;

    useEffect(() => {
        initialLoad && data && setInitialLoad(false);
        //@unreleased updated to set data to sorted state on load.
        setSortedData(data.items);
    }, [data]);

    useEffect(() => {
        if (isLoading) {
            // we need to set the overlay width in JS because tables only respect 'position: relative' in FireFox
            if (tableRef.current) {
                setOverlayWidth(tableRef.current.getBoundingClientRect().width);
            }
            setLoadingOverlay(styles.appear);
        }
        if (!isLoading && loadingOverlay) {
            setLoadingOverlay(styles.disappear);
            const timeoutId = setTimeout(() => setLoadingOverlay(false), 100);
            return () => clearTimeout(timeoutId);
        }
    }, [isLoading]);

    useEffect(() => {
        let timeoutId;
        if (updateErrors.errors.length) {
            setErrorOverlay(styles.appear);
            timeoutId = setTimeout(
                () =>
                    document.getElementById(styles.updateError).scrollIntoView?.({behavior: 'smooth', block: 'center'}),
                100
            );
        } else if (errorOverlay) {
            setErrorOverlay(styles.disappear);
            timeoutId = setTimeout(() => setErrorOverlay(false), 100);
        }
        return () => clearTimeout(timeoutId);
    }, [updateErrors.errors]);

    const clearUpdateErrors = () => {
        setUpdateErrors({errors: [], successes: []});
    };

    //@unreleased declare function to set column and direction for db & sorting.
    const setSortDirectionForColumn = (column, direction, event) => {
        event.preventDefault();
        setSort((previousState) => {
            return {
                ...previousState,
                sortColumn: column,
                sortDirection: direction === 'asc' ? 'desc' : 'asc',
            };
        });
        handleSortData(column, direction, data);
    };

    // //@unreleased sort data based on type and direction.
    const handleSortData = (column, sortDirection, data) => {
        if (sortDirection === 'asc') {
            column === 'createdAt'
                ? setSortedData([...data?.items].sort((a, b) => a[sortColumn] - b[sortColumn]))
                : setSortedData(
                      [...data?.items].sort((a, b) =>
                          a[sortColumn]?.toString().localeCompare(b[sortColumn]?.toString(), {
                              numeric: a[sortColumn] !== isNaN,
                          })
                      )
                  );
        } else if (sortDirection === 'desc') {
            column === 'createdAt'
                ? setSortedData([...data?.items].sort((a, b) => b[sortColumn] - a[sortColumn]))
                : setSortedData(
                      [...data?.items].sort((a, b) =>
                          b[sortColumn]?.toString().localeCompare(a[sortColumn]?.toString(), {
                              numeric: b[sortColumn] !== isNaN,
                          })
                      )
                  );
        }
    };

    return (
        <>
            {initialLoad && !error ? (
                <div className={styles.initialLoad}>
                    <div role="dialog" aria-labelledby="giveListTableLoadingMessage" className={cx(styles.tableGroup)}>
                        <Spinner size={'large'} />
                        <h2 id="giveListTableLoadingMessage">{sprintf(__('Loading %s', 'give'), pluralName)}</h2>
                    </div>
                </div>
            ) : (
                <div
                    role="group"
                    aria-labelledby="giveListTableCaption"
                    aria-describedby="giveListTableMessage"
                    className={styles.tableGroup}
                    tabIndex={0}
                >
                    {loadingOverlay && (
                        <div
                            className={cx(styles.overlay, loadingOverlay)}
                            style={{width: overlayWidth && overlayWidth + 'px'}}
                        >
                            <Spinner size={'medium'} />
                        </div>
                    )}
                    <table ref={tableRef} className={styles.table}>
                        <caption id="giveListTableCaption" className={styles.tableCaption}>
                            {title}
                        </caption>
                        <thead>
                            <tr>
                                <th
                                    scope="col"
                                    aria-sort="none"
                                    className={cx(styles.tableColumnHeader, styles.selectAll)}
                                    data-column="select"
                                >
                                    <BulkActionCheckboxAll pluralName={pluralName} data={data} />
                                </th>
                                <>
                                    {columns?.map((column) => (
                                        <th
                                            scope="col"
                                            aria-sort="none"
                                            className={cx(styles.tableColumnHeader, {
                                                [styles[align]]: !column?.alignColumn,
                                                [styles.center]: column?.alignColumn === 'center',
                                                [styles.start]: column?.alignColumn === 'start',
                                            })}
                                            data-column={column.name}
                                            key={column.name}
                                            style={{inlineSize: column?.inlineSize || '8rem'}}
                                        >
                                            {/*{@unreleased new Table Sorting component.}*/}
                                            <TableSort
                                                column={column}
                                                sort={sort}
                                                onClick={(event) =>
                                                    column.isSortable &&
                                                    setSortDirectionForColumn(column.name, sortDirection, event)
                                                }
                                            />
                                        </th>
                                    ))}
                                </>
                            </tr>
                        </thead>
                        <tbody className={styles.tableContent}>
                            <ListTableRows
                                columns={columns}
                                //@unreleased updated to pass sorted data.
                                data={sortedData}
                                isLoading={isLoading}
                                singleName={singleName}
                                rowActions={rowActions}
                                parameters={parameters}
                                setUpdateErrors={setUpdateErrors}
                                align={align}
                            />
                        </tbody>
                    </table>
                    {errorOverlay && (
                        <div className={cx(styles.overlay, errorOverlay)}>
                            <div id={styles.updateError} role="dialog" aria-labelledby="giveListTableErrorMessage">
                                {Boolean(updateErrors.successes.length) && (
                                    <span>
                                        {updateErrors.successes.length +
                                            ' ' +
                                            // translators:
                                            // Like '1 item was updated successfully'
                                            // or '3 items were updated successfully'
                                            _n(
                                                sprintf('%s was updated successfully.', singleName),
                                                sprintf('%s were updated successfully.', pluralName),
                                                updateErrors.successes.length,
                                                'give'
                                            )}
                                    </span>
                                )}
                                <span id="giveListTableErrorMessage">
                                    {updateErrors.errors.length +
                                        ' ' +
                                        _n(
                                            `${singleName} couldn't be updated.`,
                                            `${pluralName} couldn't be updated.`,
                                            updateErrors.errors.length,
                                            'give'
                                        )}
                                </span>
                                <button
                                    type="button"
                                    className={cx('dashicons dashicons-dismiss', styles.dismiss)}
                                    onClick={clearUpdateErrors}
                                >
                                    <span className="give-visually-hidden">{__('dismiss', 'give')}</span>
                                </button>
                            </div>
                        </div>
                    )}
                    <div id="giveListTableMessage">
                        {isEmpty && (
                            <div role="status" className={styles.statusMessage}>
                                {sprintf(__('No %s found.', 'give'), pluralName)}
                            </div>
                        )}
                        {error && (
                            <>
                                <div role="alert" className={styles.statusMessage}>
                                    {sprintf(__('There was a problem retrieving the %s.', 'give'), pluralName)}
                                </div>
                                <div className={styles.statusMessage}>
                                    <a href={window.location.href.toString()}>
                                        {__('Click here to reload the page.', 'give')}
                                    </a>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            )}
        </>
    );
};
