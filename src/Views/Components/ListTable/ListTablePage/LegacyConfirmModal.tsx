import {useState} from 'react';
import type {ReactNode} from 'react';
import {__} from '@wordpress/i18n';
import {Modal} from '@wordpress/components';
import BulkActionConfirm from '../BulkActionConfirm';
import {normalizeActionResult} from '../adapters/normalizeActionResult';
import type {ReportOutcome} from './types';

/**
 * A confirm request from a legacy row action, through `ShowConfirmModalContext`.
 *
 * @since TBD
 */
export type LegacyConfirmRequest = {
    label: string;
    confirm: (...args: unknown[]) => ReactNode;
    action: (...args: unknown[]) => Promise<unknown>;
    type?: 'normal' | 'warning' | 'danger' | 'custom' | null;
    confirmButtonText?: string;
};

/**
 * @since TBD
 */
export interface LegacyConfirmModalProps {
    request: LegacyConfirmRequest;
    onClose: () => void;
    report: ReportOutcome;
    refresh: () => Promise<unknown>;
}

/**
 * The confirm modal legacy row actions open with `ShowConfirmModalContext`. The action reports its
 * own result (through `setUpdateErrors`); a thrown error is reported here.
 *
 * @since TBD
 */
export default function LegacyConfirmModal({request, onClose, report, refresh}: LegacyConfirmModalProps) {
    const [isBusy, setIsBusy] = useState(false);

    const onConfirm = async () => {
        setIsBusy(true);

        try {
            await request.action([]);
        } catch (error) {
            // A row action changes one record.
            report(normalizeActionResult(error, [0]), request.label);
        }

        onClose();
        await refresh();
    };

    return (
        <Modal title={request.label} onRequestClose={onClose}>
            <BulkActionConfirm
                message={request.confirm([], [], true, onClose)}
                confirmLabel={request.confirmButtonText ?? __('Confirm', 'give')}
                variant={request.type === 'danger' || request.type === 'warning' ? request.type : 'normal'}
                isBusy={isBusy}
                onConfirm={onConfirm}
                onCancel={onClose}
            />
        </Modal>
    );
}
