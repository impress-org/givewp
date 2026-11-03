import {__} from '@wordpress/i18n';

/**
 * One layout request (a save or a clear), started when the queue gets to it.
 *
 * @since TBD
 */
export type LayoutWrite = () => Promise<unknown>;

/**
 * The notice shown when a layout write fails.
 *
 * @since TBD
 */
export type LayoutErrorNotice = {id: string; type: 'error'; isDismissible: boolean; content: string};

/**
 * Writes run one after another, so an older write never finishes after a newer one. Kept for the page
 * view, not per table.
 */
let writes: Promise<unknown> = Promise.resolve();

/**
 * Whether the failed-write notice was shown in this page view, so a remounted table (an embedded tab
 * opened again) does not show it twice.
 */
let hasNotified = false;

/**
 * Starts the write after the writes before it have finished.
 *
 * @since TBD
 */
export function enqueueLayoutWrite(write: LayoutWrite): void {
    writes = writes.then(write);
}

/**
 * Starts the write at once, without waiting for the queue. Used when the page is closing, because the
 * queue may never get to it. Later writes wait for this one.
 *
 * @since TBD
 */
export function sendLayoutNow(write: LayoutWrite): void {
    writes = write();
}

/**
 * Shows the failed-write notice, once per page view.
 *
 * @since TBD
 */
export function notifyLayoutErrorOnce(addNotice: (notice: LayoutErrorNotice) => void): void {
    if (hasNotified) {
        return;
    }

    hasNotified = true;
    addNotice({
        id: 'givewp-list-table-layout-error',
        type: 'error',
        isDismissible: true,
        content: __('Give could not save your table layout. Your changes stay until you leave this page.', 'give'),
    });
}

/**
 * Starts a new page view: an empty queue and no notice shown yet. For tests.
 *
 * @since TBD
 */
export function resetLayoutWriteQueue(): void {
    writes = Promise.resolve();
    hasNotified = false;
}
