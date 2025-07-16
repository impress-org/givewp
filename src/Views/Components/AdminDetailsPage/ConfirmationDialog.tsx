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
    variant?: 'error' | 'regular';
    className?: string;
    actionLabel: string;
    children: React.ReactNode;
    isConfirming?: boolean;
    spinner?: 'regular' | 'arc' | 'none';
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
    spinner ='none',
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
                        {actionLabel}
                        {isConfirming ? (spinner === 'arc' ? <ArcSpinner /> : <Spinner />) : null}
                    </button>
                </div>
            </>
        </ModalDialog>
    );
}
