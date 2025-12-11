import cx from 'classnames';
import {__} from '@wordpress/i18n'
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {ErrorIcon, WarningIcon} from './Icons';
import styles from './AdminDetailsPage.module.scss'
import { Spinner } from '@wordpress/components';
import ArcSpinner from '@givewp/src/Admin/components/Spinner/ArcSpinner';

export type ConfirmationDialogProps = {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: () => void;
    title: string;
    icon?: React.ReactElement;
    variant?: 'error' | 'regular' | 'syncing';
    className?: string;
    actionLabel: string;
    showCancelButton?: boolean;
    children: React.ReactNode;
    isConfirming?: boolean;
    spinner?: 'regular' | 'arc' | 'none';
    footer?: React.ReactNode;
}

/**
 * @since 4.8.0 Add showCancelButton prop
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
    showCancelButton = true,
    children,
    isConfirming = false,
    spinner ='none',
    footer,
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
                    {showCancelButton && (
                        <button
                            className={styles.cancelButton}
                            onClick={handleClose}
                        >
                            {__('Cancel', 'give')}
                        </button>
                    )}
                    <button
                        className={cx(styles.confirmButton, styles[`confirmButton--${variant}`])}
                        onClick={handleConfirm}
                        disabled={isConfirming}
                    >
                        {actionLabel}
                        {isConfirming ? (spinner === 'arc' ? <ArcSpinner /> : <Spinner />) : null}
                    </button>
                </div>
                <div className={styles.confirmationDialogFooter}>
                    {footer}
                </div>
            </>
        </ModalDialog>
    );
}
