import React, {useEffect, useRef} from 'react';
import {A11yDialog} from 'react-a11y-dialog';
import A11yDialogInstance from 'a11y-dialog';
import cx from 'classnames';

import styles from './style.module.scss';
import ExitIcon from '@givewp/components/AdminUI/Icons/ExitIcon';

export type ModalDialogProps = {
    open: boolean;
    title: string;
    children: React.ReactNode;
    handleClose(): void;
    onClose: () => void;
};

export default function ModalDialog({open, children, title, onClose, handleClose}: ModalDialogProps) {
    const dialog = useRef() as {current: A11yDialogInstance};

    useEffect(() => {
        if (!dialog.current) {
            return;
        }

        if (open) {
            dialog.current.show();
        } else {
            dialog.current.hide();
            onClose && onClose();
        }
    }, [open]);

    useEffect(() => {
        if (!dialog.current) {
            return;
        }

        dialog.current.on('hide', () => {
            onClose && onClose();
        });
    }, [dialog.current]);

    return (
        <A11yDialog
            id="givewp-modal-dialog"
            dialogRef={(instance) => (dialog.current = instance)}
            title={title}
            classNames={{
                container: styles.container,
                overlay: styles.overlay,
                dialog: cx(styles.dialog, {}),
                closeButton: 'hidden',
                title: 'hidden',
            }}
        >
            <div className={styles.dialogTitle}>
                <p aria-labelledby={title}>{title}</p>
                <button type="button" onClick={() => handleClose()}>
                    <ExitIcon />
                </button>
            </div>
            <div className={styles.modalContentContainer}>{open ? children : null}</div>
        </A11yDialog>
    );
}
