import cx from 'classnames';
import {__} from '@wordpress/i18n'
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {ErrorIcon, WarningIcon} from './Icons';
import styles from './AdminDetailsPage.module.scss'

/**
 * @unreleased
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
}: {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: () => void;
    title: string;
    icon?: React.ReactElement;
    variant?: 'error' | 'regular';
    className?: string;
    actionLabel: string;
    children: React.ReactNode;
}) {
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

                    >
                        {__('Cancel', 'give')}
                    </button>
                    <button
                        className={cx(styles.confirmButton, styles[`confirmButton--${variant}`])}
                        onClick={handleConfirm}
                    >
                        {actionLabel}
                    </button>
                </div>
            </>
        </ModalDialog>
    );
}
