import {__, sprintf} from '@wordpress/i18n';
import {Button} from '@wordpress/components';
import './styles.scss';

/**
 * @since TBD
 */
export interface ListTableErrorStateProps {
    pluralName: string;
    onRetry: () => void;
}

/**
 * Shown in place of the rows when a list request fails. Retrying keeps the current view.
 *
 * @since TBD
 */
export default function ListTableErrorState({pluralName, onRetry}: ListTableErrorStateProps) {
    return (
        <div className="givewp-list-table-error-state" role="alert">
            {/* translators: %s: record type, plural (for example "donations") */}
            <p>{sprintf(__('There was a problem retrieving the %s.', 'give'), pluralName)}</p>
            <Button variant="secondary" onClick={onRetry}>
                {__('Try again', 'give')}
            </Button>
        </div>
    );
}
