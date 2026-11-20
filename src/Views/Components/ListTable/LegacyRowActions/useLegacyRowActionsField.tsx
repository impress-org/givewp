import {useMemo} from 'react';
import {__} from '@wordpress/i18n';
import type {Field} from '@wordpress/dataviews';
import type ListTableApi from '../api';
import type {
    LegacyRowActionArgs,
    LegacyRowActions as LegacyRowActionsRender,
    ListTableItem,
    ListTableParameters,
    ListTableResponse,
    ReportOutcome,
} from '../ListTablePage/types';
import {normalizeActionResult} from '../adapters/normalizeActionResult';
import LegacyRowActions, {LEGACY_ROW_ACTIONS_FIELD} from './index';

type UseLegacyRowActionsFieldArgs = {
    data: ListTableResponse | undefined;
    parameters: ListTableParameters;
    listTableApi: ListTableApi;
    report: ReportOutcome;
    refresh: () => Promise<unknown>;
};

/**
 * The record id a legacy row action button carries in `data-actionid`.
 */
const actionIdOf = (event: {target: EventTarget}) =>
    (event.target as HTMLElement).closest<HTMLElement>('[data-actionid]')?.dataset.actionid;

/**
 * The "Actions" column for a row-actions function written before the list engine change, with the
 * arguments that function always received. `null` when the screen has no such function.
 *
 * @since TBD
 */
export default function useLegacyRowActionsField(
    render: LegacyRowActionsRender | null,
    {data, parameters, listTableApi, report, refresh}: UseLegacyRowActionsFieldArgs
): Field<ListTableItem> | null {
    return useMemo(() => {
        if (!render) {
            return null;
        }

        const argsFor = (item: ListTableItem): LegacyRowActionArgs => ({
            data,
            item,
            parameters,
            listTableApi,
            removeRow: (callback) => async (event) => {
                await callback(actionIdOf(event));
                await refresh();
            },
            addRow: (callback) => async (event) => {
                await callback(actionIdOf(event));
                await refresh();
            },
            setUpdateErrors: (response) => {
                report(normalizeActionResult(response, []));
                refresh();
            },
        });

        return {
            id: LEGACY_ROW_ACTIONS_FIELD,
            label: __('Actions', 'give'),
            enableHiding: false,
            enableSorting: false,
            render: ({item}) => <LegacyRowActions render={render} args={argsFor(item)} />,
        };
    }, [render, data, parameters, listTableApi, report, refresh]);
}
