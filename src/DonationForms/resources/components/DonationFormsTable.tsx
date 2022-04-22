import {useEffect, useState} from 'react';
import {__, _n} from '@wordpress/i18n';
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

    async function mutateForm(ids, endpoint, method) {
        try {
            const response = await fetchWithArgs(endpoint, {ids}, method);
            // if we just removed the last entry from the page and we're not on the first page, go back a page
            if (
                !response.errors.length &&
                data.forms.length == 1 &&
                data.totalPages > 1 &&
                (endpoint == '/delete' || endpoint == '/trash' || endpoint == '/restore')
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
                        <h2>{__('Loading donation forms', 'give')}</h2>
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
                            {__('Donation Forms', 'give')}
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" aria-sort="none" className={styles.tableColumnHeader} data-column="id">
                                    {__('ID', 'give')}
                                </th>
                                <th
                                    scope="col"
                                    aria-sort="none"
                                    className={styles.tableColumnHeader}
                                    data-column="name"
                                >
                                    {__('Name', 'give')}
                                </th>
                                <th
                                    scope="col"
                                    aria-sort="none"
                                    className={styles.tableColumnHeader}
                                    data-column="amount"
                                >
                                    {__('Amount', 'give')}
                                </th>
                                <th
                                    scope="col"
                                    aria-sort="none"
                                    className={styles.tableColumnHeader}
                                    data-column="goal"
                                >
                                    {__('Goal', 'give')}
                                </th>
                                <th
                                    scope="col"
                                    aria-sort="none"
                                    className={styles.tableColumnHeader}
                                    data-column="donations"
                                >
                                    {__('Donations', 'give')}
                                </th>
                                <th
                                    scope="col"
                                    aria-sort="none"
                                    className={styles.tableColumnHeader}
                                    data-column="revenue"
                                >
                                    {__('Revenue', 'give')}
                                </th>
                                <th
                                    scope="col"
                                    aria-sort="none"
                                    className={styles.tableColumnHeader}
                                    data-column="shortcode"
                                >
                                    {__('Shortcode', 'give')}
                                </th>
                                <th
                                    scope="col"
                                    aria-sort="ascending"
                                    className={styles.tableColumnHeader}
                                    data-column="date"
                                >
                                    {__('Date', 'give')}
                                </th>
                                <th
                                    scope="col"
                                    aria-sort="none"
                                    className={styles.tableColumnHeader}
                                    data-column="status"
                                >
                                    {__('Status', 'give')}
                                </th>
                            </tr>
                        </thead>
                        <tbody className={styles.tableContent}>
                            <DonationFormTableRows listParams={listParams} mutateForm={mutateForm} status={status} />
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
                                            _n(
                                                'form was updated successfully',
                                                'forms were updated successfully.',
                                                successes.length,
                                                'give'
                                            )}
                                    </span>
                                )}
                                <span>
                                    {errors.length +
                                        ' ' +
                                        _n(
                                            "form couldn't be updated.",
                                            "forms couldn't be updated.",
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
                                    <span className="give-visually-hidden">dismiss</span>
                                </button>
                            </div>
                        </div>
                    )}
                    <div id="giveDonationFormsTableMessage">
                        {isEmpty && (
                            <div className={styles.statusMessage}>{__('No donation forms found.', 'give')}</div>
                        )}
                        {error && (
                            <>
                                <div className={styles.statusMessage}>
                                    {__('There was a problem retrieving the donation forms.', 'give')}
                                </div>
                                <div className={styles.statusMessage}>
                                    {__('Click', 'give') + ' '}
                                    <a href={'edit.php?post_type=give_forms&page=give-forms'}>{__('here', 'give')}</a>
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
