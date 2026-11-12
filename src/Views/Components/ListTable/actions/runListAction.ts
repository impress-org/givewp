import type {ActionOutcome, ListActionContext} from '../ListTablePage/types';
import {normalizeActionResult} from '../adapters/normalizeActionResult';

/**
 * @since TBD
 */
export type RunListActionArgs = {
    /** Calls the endpoint. Its reply, or the error it throws, is counted by `normalizeActionResult`. */
    run: () => Promise<unknown>;
    ids: Array<string | number>;
    /** The action name the result notices use. */
    label: string;
    /** The snackbar text when records changed, in place of the generic "updated" message. */
    successMessage?: string;
    /** Called once the request is done and before the result shows, for example to close a modal. */
    onDone?: () => void;
};

/**
 * Runs a list action the same way everywhere: calls the endpoint, reports what changed and what
 * failed, clears the selection when everything succeeded, and reloads the list.
 *
 * @since TBD
 */
export async function runListAction(
    {run, ids, label, successMessage, onDone}: RunListActionArgs,
    {report, refresh, clearSelection}: ListActionContext
): Promise<void> {
    let outcome: ActionOutcome;

    try {
        outcome = normalizeActionResult(await run(), ids);
    } catch (error) {
        outcome = normalizeActionResult(error, ids);
    }

    if (outcome.changed > 0 && successMessage) {
        outcome = {...outcome, successMessage};
    }

    onDone?.();
    report(outcome, label);

    if (outcome.failed === 0 && !outcome.notice) {
        clearSelection();
    }

    await refresh();
}
