import {useEffect, useState} from 'react';
import {__, _n, sprintf} from '@wordpress/i18n';
import cx from 'classnames';

import styles from './style.module.scss';
import ListTableRows from './ListTableRows';
import {Spinner} from '../index';

export interface ListTableProps {
    columns: Array<ListTableColumn>;
    singleName?: string;
    pluralName?: string;
    title: string;
    data: {items: Array<{}>};
    error?: {};
    isValidating?: Boolean;
    parameters?: any;
}

export interface ListTableColumn {
    name: string;
    text: string;
    preset?: string;
    heading?: boolean;
    addClass?: string;
    render?: (item: {}) => JSX.Element|string|null;
    rowActions?: (props: {}) => JSX.Element|null;
}

export const ListTable = ({
        columns,
        singleName = __('item', 'give'),
        pluralName = __('items', 'give'),
        title,
        data,
        error = false,
        isValidating = false,
}: ListTableProps) => {
    const [updateErrors, setUpdateErrors] = useState<{errors: Array<number>, successes: Array<number>}>({errors: [], successes: []});
    const [errorOverlay, setErrorOverlay] = useState<any>(false);
    const [initialLoad, setInitialLoad] = useState<boolean>(true);
    const [loadingOverlay, setLoadingOverlay] = useState<any>(false);
    const isEmpty = !error && data?.items.length === 0;

    useEffect(() => {
        initialLoad && data && setInitialLoad(false);
    }, [data]);

    useEffect(() => {
        if (isValidating) {
            setLoadingOverlay(styles.appear);
        }
        if (!isValidating && loadingOverlay) {
            setLoadingOverlay(styles.disappear);
            const timeoutId = setTimeout(() => setLoadingOverlay(false), 100);
            return () => clearTimeout(timeoutId);
        }
    }, [isValidating]);

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
        setUpdateErrors({errors: [], successes: []})
    }

    return (
        <>

            {( initialLoad && !error ) ? (
                <div className={styles.initialLoad}>
                    <div
                        role="dialog"
                        aria-labelledby="giveListTableLoadingMessage"
                        className={cx(styles.tableGroup)}
                    >
                        <Spinner size={'large'} />
                        <h2 id="giveListTableLoadingMessage">
                            {sprintf(__('Loading %s', 'give'), pluralName)}
                        </h2>
                    </div>
                </div>
            ) : (
                <div
                    role="group"
                    aria-labelledby="giveListTableCaption"
                    aria-describedby="giveListTableMessage"
                    className={styles.tableGroup}
                >
                    <table className={styles.table}>
                        <caption id="giveListTableCaption" className={styles.tableCaption}>
                            {title}
                        </caption>
                        <thead>
                            <tr>
                                {columns.map(column =>
                                    <th
                                        scope="col"
                                        aria-sort="none"
                                        className={styles.tableColumnHeader}
                                        data-column={column.name}
                                        key={column.name}
                                    >
                                        {column.text}
                                    </th>
                                )}
                            </tr>
                        </thead>
                        <tbody className={styles.tableContent}>
                            <ListTableRows
                                columns={columns}
                                data={data}
                                isValidating={isValidating}
                            />
                        </tbody>
                    </table>
                    {loadingOverlay && (
                        <div className={cx(styles.overlay, loadingOverlay)}>
                            <Spinner size={'medium'} />
                        </div>
                    )}
                    {errorOverlay && (
                        <div className={cx(styles.overlay, errorOverlay)}>
                            <div
                                id={styles.updateError}
                                role="dialog"
                                aria-labelledby="giveListTableErrorMessage"
                            >
                                {Boolean(updateErrors.successes.length) && (
                                    <span>
                                        {updateErrors.successes.length + ' ' +
                                            // translators:
                                            // Like '1 item was updated successfully'
                                            // or '3 items were updated successfully'
                                            _n(
                                                sprintf('%s was updated successfully.', singleName),
                                                sprintf('%s were updated successfully.', pluralName),
                                                updateErrors.successes.length,
                                                'give'
                                            )
                                        }
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
                            <div className={styles.statusMessage}>
                                {sprintf(__('No %s found.', 'give'), pluralName)}
                            </div>
                        )}
                        {error && (
                            <>
                                <div className={styles.statusMessage}>
                                    {sprintf(__('There was a problem retrieving the %s.', 'give'), pluralName)}
                                </div>
                                <div className={styles.statusMessage}>
                                    {__('Click', 'give')}{' '}
                                    <a href={window.location.href.toString()}>{__('here', 'give')}</a>
                                    {' ' + __('to reload the page.')}
                                </div>
                            </>
                        )}
                    </div>
                </div>
            )}
        </>
    );
}
