import {useCallback} from 'react';
import {useDispatch} from '@wordpress/data';
import {__, _n, sprintf} from '@wordpress/i18n';
import '../../AdminDetailsPage/store';
import type {ActionOutcome} from '../ListTablePage/types';

const ERROR_NOTICE_ID = 'givewp-list-table-action-error';

/**
 * Shows the result of a row or bulk action with the shared notifications store: a snackbar that
 * hides by itself for what changed, and an error notice that stays until dismissed for what failed.
 * Running another action removes the previous error notice.
 *
 * @since TBD
 */
export default function useListTableActionNotices(singleName: string, pluralName: string) {
    const {addSnackbarNotice, addNotice, dismissNotification} = useDispatch('givewp/admin-details-page-notifications');

    return useCallback(
        ({changed, failed, message}: ActionOutcome, actionLabel?: string) => {
            dismissNotification(ERROR_NOTICE_ID);

            if (changed > 0) {
                const records = changed === 1 ? singleName : pluralName;

                addSnackbarNotice({
                    id: `givewp-list-table-action-${Date.now()}`,
                    content: actionLabel
                        ? /* translators: 1: action name, 2: number of records, 3: record type (singular or plural) */
                          sprintf(__('%1$s: %2$d %3$s updated.', 'give'), actionLabel, changed, records)
                        : /* translators: 1: number of records, 2: record type (singular or plural) */
                          sprintf(__('%1$d %2$s updated.', 'give'), changed, records),
                });
            }

            if (failed > 0) {
                addNotice({
                    id: ERROR_NOTICE_ID,
                    type: 'error',
                    content:
                        message ??
                        sprintf(
                            /* translators: 1: number of records, 2: record type (singular or plural) */
                            _n('%1$d %2$s could not be updated.', '%1$d %2$s could not be updated.', failed, 'give'),
                            failed,
                            failed === 1 ? singleName : pluralName
                        ),
                });
            }
        },
        [addSnackbarNotice, addNotice, dismissNotification, singleName, pluralName]
    );
}
