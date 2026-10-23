import {Button} from '@wordpress/components';
import './styles.scss';

/**
 * @since TBD
 */
export interface ListTableEmptyStateProps {
    message: string;
    actionLabel: string;
    onAction: () => void;
    /** An error is announced at once; an empty result politely. */
    isError?: boolean;
}

/**
 * What the list shows in place of rows when there are none to show: a filter that matches nothing,
 * or a request that failed. A site with no records at all keeps the screen's onboarding blank slate.
 *
 * @since TBD
 */
export default function ListTableEmptyState({
    message,
    actionLabel,
    onAction,
    isError = false,
}: ListTableEmptyStateProps) {
    return (
        <div className="givewp-list-table-empty-state" role={isError ? 'alert' : 'status'}>
            <p>{message}</p>
            <Button variant="secondary" onClick={onAction}>
                {actionLabel}
            </Button>
        </div>
    );
}
