import cx from 'classnames';
import {__} from '@wordpress/i18n'
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {ErrorIcon, WarningIcon} from './Icons';
import styles from './AdminDetailsPage.module.scss'
import { Spinner } from '@wordpress/components';

export type ConfirmationDialogProps = {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: () => void;
    title: string;
    icon?: React.ReactElement;
    variant?: 'error' | 'regular';
    className?: string;
    actionLabel: string;
    children: React.ReactNode;
    isConfirming?: boolean;
}

/**
 * @since 4.4.0
 */
export default function ConfirmationDialog({
    isOpen,
    title,
    icon,
    variant = 'error',
    handleClose,
    handleConfirm,
    className,
    actionLabel,
    children,
    isConfirming = false,
}: ConfirmationDialogProps) {
    return (
        <ModalDialog
            icon={icon || (variant === 'error' && <ErrorIcon />)}
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={title}
            wrapperClassName={className}
        >
            <>
                <div className={styles.confirmationDialogContent}>
                    {children}
                </div>
                <div className={styles.confirmationDialogButtons}>
                    <button
                        className={styles.cancelButton}
                        onClick={handleClose}
                        disabled={isConfirming}
                    >
                        {__('Cancel', 'give')}
                    </button>
                    <button
                        className={cx(styles.confirmButton, styles[`confirmButton--${variant}`])}
                        onClick={handleConfirm}
                        disabled={isConfirming}
                    >
                        {actionLabel} {isConfirming && <Spinner />}
                    </button>
                </div>
            </>
        </ModalDialog>
    );
}
