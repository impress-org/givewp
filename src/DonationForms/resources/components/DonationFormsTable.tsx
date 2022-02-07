import {useEffect, useState} from 'react';
import {__, _n} from '@wordpress/i18n';
import { useSWRConfig, unstable_serialize } from 'swr';
import cx from 'classnames';

import styles from './DonationFormsTable.module.scss';
import Pagination from './Pagination.js';
import DonationFormTableRows from './DonationFormsTableRows';
import {Spinner} from "../../../Views/Components";
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
    const [errors, setErrors] = useState<number>(0);
    const [successes, setSuccesses] = useState<number>(0);
    const [initialLoad, setInitialLoad] = useState<boolean>(true);
    const [overlay, setOverlay] = useState<any>(false);
    const listParams = {
        page,
        perPage,
        status,
        search,
    };
    const {data, error, isValidating} = useDonationForms(listParams);
    const { mutate, cache } = useSWRConfig();
    const isEmpty = !error && data?.forms.length === 0;
    useEffect(() => {
        setPage(1)
    }, [status, search]);
    useEffect(() => {
        initialLoad && data && setInitialLoad(false)
    }, [data]);
    useEffect( () => {
        if(isValidating && !cache.get(unstable_serialize(listParams))){
            setOverlay(styles.appear);
        }
        if(!isValidating && overlay){
            setOverlay(styles.disappear);
            const timeoutId = setTimeout(() => setOverlay(false), 100);
            return () => clearTimeout(timeoutId);
        }
    }, [isValidating])

    async function mutateForm(ids, endpoint, method) {
        try {
            const response = await fetchWithArgs(endpoint, {ids}, method);
            // if we just removed the last entry from the page and we're not on the first page, go back a page
            if( !response.errors && data.forms.length == 1 && data.totalPages > 1
                && (endpoint == '/delete' || endpoint == '/trash' || endpoint == '/restore') )
            {
                setPage(page - 1);
            }
            // otherwise, revalidate current page
            else
            {
                await mutate(listParams);
            }
            //revalidate all pages after the current page
            const mutations = [];
            for (let i = page + 1; i <= data.totalPages; i++) {
                mutations.push(mutate({...listParams, page: i}));
            }
            setErrors(response.errors);
            setSuccesses(response.successes);
        } catch (error) {
            setErrors(ids.split(',').length);
            setSuccesses(0);
        }

    }


    return (
        <>
            {!!errors && (
                <div className={styles.updateError}>
                    {!!successes && (
                        <span>
                            {successes +
                                ' ' +
                                _n(
                                    'form was updated successfully',
                                    'forms were updated successfully.',
                                    successes,
                                    'give'
                                )}
                        </span>
                    )}
                    <span>
                        {errors + ' ' + _n("form couldn't be updated.", "forms couldn't be updated.", errors, 'give')}
                    </span>
                    <button
                        type="button"
                        className={cx('dashicons dashicons-dismiss', styles.dismiss)}
                        onClick={() => {
                            setErrors(0);
                            setSuccesses(0);
                        }}
                    >
                        <span className="give-visually-hidden">dismiss</span>
                    </button>
                </div>
            )}
            <div className={styles.pageActions}>
                <Pagination
                    currentPage={page}
                    totalPages={data ? data.totalPages : 1}
                    disabled={!data}
                    totalItems={data ? data.totalForms : -1}
                    setPage={setPage}
                />
            </div>
            {initialLoad ?
                <div className={styles.initialLoad}>
                    <div className={cx(styles.tableGroup)}>
                        <Spinner size={'large'}/>
                        <h2>Loading donation forms</h2>
                    </div>
                </div>
                :
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
                            <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                {__('ID', 'give')}
                            </th>
                            <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                {__('Name', 'give')}
                            </th>
                            <th
                                scope="col"
                                aria-sort="none"
                                className={styles.tableColumnHeader}
                                style={{textAlign: 'end'}}
                            >
                                {__('Amount', 'give')}
                            </th>
                            <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                {__('Goal', 'give')}
                            </th>
                            <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                {__('Donations', 'give')}
                            </th>
                            <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                {__('Revenue', 'give')}
                            </th>
                            <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                {__('Shortcode', 'give')}
                            </th>
                            <th scope="col" aria-sort="ascending" className={styles.tableColumnHeader}>
                                {__('Date', 'give')}
                            </th>
                            <th scope="col" aria-sort="none" className={styles.tableColumnHeader}>
                                {__('Status', 'give')}
                            </th>
                        </tr>
                        </thead>
                        <tbody className={styles.tableContent}>
                        <DonationFormTableRows
                            listParams={listParams}
                            mutateForm={mutateForm}
                            status={status}
                        />
                        {overlay &&
                            <tr className={cx(styles.loadingOverlay, overlay)}>
                                <Spinner size={'medium'} />
                            </tr>
                        }
                        </tbody>
                    </table>
                    <div id="giveDonationFormsTableMessage">
                        {isEmpty &&
                            <div className={styles.statusMessage}>{__('No donation forms found.', 'give')}</div>
                        }
                        {(error && !isValidating) &&
                            <>
                                <div className={styles.statusMessage}>{__('There was a problem retrieving the donation forms.', 'give')}</div>
                                <div className={styles.statusMessage}>{__('Click', 'give') + ' '}
                                    <a href={'edit.php?post_type=give_forms&page=give-forms'}>{__('here', 'give')}</a>
                                    {' ' + __('to reload the page.')}
                                </div>
                            </>
                        }
                    </div>
                </div>
            }
        </>
    );
}
