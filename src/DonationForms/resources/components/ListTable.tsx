import {useEffect, useState} from 'react';
import {__, _n, sprintf} from '@wordpress/i18n';
import {useSWRConfig, unstable_serialize} from 'swr';
import cx from 'classnames';

import styles from './ListTable.module.scss';
import {columns} from './DonationForms';
import Pagination from './Pagination.js';
import DonationFormTableRows from './ListTableRows';
import {Spinner} from '../../../Views/Components';
import {fetchWithArgs, useDonationForms} from '../api';

interface ListTableProps {
    filters: {};
    search: string;
}

const singleName = __('donation form', 'give');
const pluralName = __('donation forms', 'give');
const pluralTitleCase = __('Donation Forms', 'give');


// Todo: recursively freeze table setup props so they are stable between renders
// e.g. Object.freeze(columns); then do that recursively for properties

export default function ListTable({filters, search}: ListTableProps) {
    const [page, setPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(10);
    const [errors, setErrors] = useState<[]>([]);
    const [successes, setSuccesses] = useState<[]>([]);
    const [initialLoad, setInitialLoad] = useState<boolean>(true);
    const [loadingOverlay, setLoadingOverlay] = useState<any>(false);
    const [errorOverlay, setErrorOverlay] = useState<any>(false);
    const listParams = {
        page,
        perPage,
        search,
        ...filters
    };
    const {data, error, isValidating} = useDonationForms(listParams);
    const {mutate, cache} = useSWRConfig();
    const isEmpty = !error && data?.items.length === 0;

    useEffect(() => {
        setPage(1);
    }, [filters, search]);

    useEffect(() => {
        initialLoad && data && setInitialLoad(false);
    }, [data]);

    useEffect(() => {
        if (isValidating && !cache.get(unstable_serialize(listParams))) {
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
        if (errors.length) {
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
    }, [errors]);

    async function mutateForm(ids, endpoint, method, remove = false) {
        try {
            const response = await fetchWithArgs(endpoint, {ids}, method);
            // if we just removed the last entry from the page and we're not on the first page, go back a page
            if (
                remove &&
                !response.errors.length &&
                data.items.length == 1 &&
                data.totalPages > 1
            ) {
                setPage(page - 1);
            }
            // otherwise, revalidate current page
            else {
                await mutate(listParams);
            }
            //revalidate all pages after the current page
            const mutations = [];
            for (let i = page + 1; i <= data.totalPages; i++) {
                mutations.push(mutate({...listParams, page: i}));
            }
            setErrors(response.errors);
            setSuccesses(response.successes);
            return response;
        } catch (error) {
            setErrors(ids.split(','));
            setSuccesses([]);
            return {errors: ids.split(','), successes: []};
        }
    }

    return (
        <>
            <div className={styles.pageActions}>
                <Pagination
                    currentPage={page}
                    totalPages={data ? data.totalPages : 1}
                    disabled={!data}
                    totalItems={data ? parseInt(data.totalItems) : -1}
                    setPage={setPage}
                />
            </div>
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
                            {pluralTitleCase}
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
                            <DonationFormTableRows
                                listParams={listParams}
                                mutateForm={mutateForm}
                                columns={columns}
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
                                {!!successes.length && (
                                    <span>
                                        {successes.length +
                                            ' ' +
                                            // translators:
                                            // Like '1 item was updated successfully'
                                            // or '3 items were updated successfully'
                                            _n(
                                                sprintf('%s was updated successfully.', singleName),
                                                sprintf('%s were updated successfully.', pluralName),
                                                successes.length,
                                                'give'
                                            )}
                                    </span>
                                )}
                                <span id="giveListTableErrorMessage">
                                    {errors.length +
                                        ' ' +
                                        _n(
                                            `${singleName} couldn't be updated.`,
                                            `${pluralName} couldn't be updated.`,
                                            errors.length,
                                            'give'
                                        )}
                                </span>
                                <button
                                    type="button"
                                    className={cx('dashicons dashicons-dismiss', styles.dismiss)}
                                    onClick={() => {
                                        setErrors([]);
                                        setSuccesses([]);
                                    }}
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
            <div className={styles.pageActions}>
                <Pagination
                    currentPage={page}
                    totalPages={data ? data.totalPages : 1}
                    disabled={!data}
                    totalItems={data ? parseInt(data.totalItems) : -1}
                    setPage={setPage}
                />
            </div>
        </>
    );
}
