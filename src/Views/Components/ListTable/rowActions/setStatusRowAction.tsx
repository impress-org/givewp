import type {ReactNode} from 'react';
import {__} from '@wordpress/i18n';
import type ListTableApi from '../api';
import type {ListTableParameters, ListTableRowAction} from '../ListTablePage/types';
import SetStatusDialog from '../SetStatusDialog';

/**
 * @since TBD
 */
export type SetStatusRowActionArgs = {
    api: ListTableApi;
    statuses: Array<{value: string; label: string}>;
    message: ReactNode;
    isVisible?: (parameters: ListTableParameters) => boolean;
};

/**
 * One "Set status" action for rows and selections, through the list endpoint's `/setStatus`
 * action. Replaces a "Set To …" bulk action per status.
 *
 * @since TBD
 */
export function setStatusRowAction({api, statuses, message, isVisible}: SetStatusRowActionArgs): ListTableRowAction {
    return {
        id: 'set-status',
        label: __('Set status', 'give'),
        supportsBulk: true,
        ...(isVisible && {isVisible: (data, parameters) => isVisible(parameters)}),
        Dialog: (props) => (
            <SetStatusDialog
                {...props}
                message={message}
                statuses={statuses}
                onSetStatus={(ids, status) => api.fetchWithArgs('/setStatus', {ids: ids.join(','), status}, 'POST')}
            />
        ),
    };
}
