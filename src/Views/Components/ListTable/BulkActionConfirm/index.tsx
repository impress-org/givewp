import type {ReactNode} from 'react';
import {__} from '@wordpress/i18n';
import {Button, Flex} from '@wordpress/components';
import {Interweave} from 'interweave';
import {IdBadge} from '../TableCell';
import './styles.scss';

/**
 * @since TBD
 */
export interface BulkActionConfirmProps {
    message: ReactNode;
    confirmLabel: string;
    onConfirm: () => void;
    onCancel: () => void;
    items?: Array<{id: string | number; name: string}>;
    variant?: 'normal' | 'warning' | 'danger';
    isBusy?: boolean;
    /** False while the body still needs a choice (for example a status to set). */
    canConfirm?: boolean;
}

/**
 * The body of an action's confirm modal: what will happen, the records it affects, and Cancel /
 * Confirm. Nothing changes until the admin confirms.
 *
 * @since TBD
 */
export default function BulkActionConfirm({
    message,
    confirmLabel,
    onConfirm,
    onCancel,
    items = [],
    variant = 'normal',
    isBusy = false,
    canConfirm = true,
}: BulkActionConfirmProps) {
    return (
        <div className="givewp-bulk-action-confirm">
            <div className="givewp-bulk-action-confirm__message">{message}</div>
            {items.length > 0 && (
                <ul className="givewp-bulk-action-confirm__list">
                    {items.map((item) => (
                        <li key={item.id} className="givewp-bulk-action-confirm__item">
                            <IdBadge id={item.id} /> <Interweave content={item.name} />
                        </li>
                    ))}
                </ul>
            )}
            <Flex justify="flex-end" className="givewp-bulk-action-confirm__buttons">
                <Button variant="tertiary" onClick={onCancel} disabled={isBusy}>
                    {__('Cancel', 'give')}
                </Button>
                <Button
                    variant="primary"
                    isDestructive={variant === 'danger'}
                    onClick={onConfirm}
                    isBusy={isBusy}
                    disabled={isBusy || !canConfirm}
                >
                    {confirmLabel}
                </Button>
            </Flex>
        </div>
    );
}
