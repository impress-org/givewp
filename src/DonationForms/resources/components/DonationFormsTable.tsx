import {useEffect, useState} from 'react';
import {__, _n, sprintf} from '@wordpress/i18n';
import {useSWRConfig, unstable_serialize} from 'swr';
import cx from 'classnames';

import styles from './DonationFormsTable.module.scss';
import Pagination from './Pagination.js';
import DonationFormTableRows from './DonationFormsTableRows';
import {Spinner} from '../../../Views/Components';
import {fetchWithArgs, useDonationForms} from '../api';

export enum DonationStatus {
    Any = 'any',
    Publish = 'publish',
    Pending = 'pending',
    Draft = 'draft',
    Trash = 'trash',
}

interface DonationFormsTableProps {
    statusFilter: DonationStatus;
    search: string;
}

const singleName = __('donation form', 'give');
const pluralName = __('donation forms', 'give');
const pluralTitleCase = __('Donation Forms', 'give');

const columnHeadings = [
    {
        name: 'id',
        text: __('ID', 'give')
    },
    {
        name: 'name',
        text: __('Name', 'give')
    },
    {
        name: 'amount',
        text: __('Amount', 'give')
    },
    {
        name: 'goal',
        text: __('Goal', 'give')
    },
    {
        name: 'donations',
        text: __('Donations', 'give')
    },
    {
        name: 'revenue',
        text: __('Revenue', 'give')
    },
    {
        name: 'shortcode',
        text: __('Shortcode', 'give')
    },
    {
        name: 'date',
        text: __('Date', 'give')
    },
    {
        name: 'status',
        text: __('Status', 'give')
    },
];

export default function DonationFormsTable({statusFilter: status, search}: DonationFormsTableProps) {
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
        status,
        search,
    };
    const {data, error, isValidating} = useDonationForms(listParams);
    const {mutate, cache} = useSWRConfig();
    const isEmpty = !error && data?.forms.length === 0;

    useEffect(() => {
        setPage(1);
    }, [status, search]);

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
                data.forms.length == 1 &&
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
                    totalItems={data ? parseInt(data.totalForms) : -1}
                    setPage={setPage}
                />
            </div>
            {( initialLoad && !error ) ? (
                <div className={styles.initialLoad}>
                    <div className={cx(styles.tableGroup)}>
                        <Spinner size={'large'} />
                        <h2>{sprintf(__('Loading %s', 'give'), pluralName)}</h2>
                    </div>
                </div>
            ) : (
                <div
                    role="group"
                    aria-labelledby="giveDonationFormsTableCaption"
                    aria-describedby="giveDonationFormsTableMessage"
                    className={styles.tableGroup}
                >
                    <table className={styles.table}>
                        <caption id="giveDonationFormsTableCaption" className={styles.tableCaption}>
                            {pluralTitleCase}
                        </caption>
                        <thead>
                            <tr>
                                {columnHeadings.map(heading =>
                                    <th
                                        scope="col"
                                        aria-sort="none"
                                        className={styles.tableColumnHeader}
                                        data-column={heading.name}
                                        key={heading.name}
                                    >
                                        {heading.text}
                                    </th>
                                )}
                            </tr>
                        </thead>
                        <tbody className={styles.tableContent}>
                            <DonationFormTableRows listParams={listParams} mutateForm={mutateForm} />
                        </tbody>
                    </table>
                    {loadingOverlay && (
                        <div className={cx(styles.overlay, loadingOverlay)}>
                            <Spinner size={'medium'} />
                        </div>
                    )}
                    {errorOverlay && (
                        <div className={cx(styles.overlay, errorOverlay)}>
                            <div id={styles.updateError}>
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
                                <span>
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
                    <div id="giveDonationFormsTableMessage">
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
                    totalItems={data ? parseInt(data.totalForms) : -1}
                    setPage={setPage}
                />
            </div>
        </>
    );
}
