import {MouseEventHandler, useCallback, useEffect} from 'react';
import {createPortal} from 'react-dom';
import {__} from '@wordpress/i18n';
import {ExitIcon} from '@givewp/components/AdminUI/Icons';
import styles from './style.module.scss';

export interface ModalProps {
    children: JSX.Element | JSX.Element[];
    title: string;
    isOpen?: boolean;
    insertInto?: string;
    handleClose?: MouseEventHandler;
    showHeader?: boolean;
}

export default function Modal({title, children, insertInto, handleClose, isOpen = true, showHeader = true}: ModalProps) {
    // ESC key closes modal
    const closeModal = useCallback(e => {
        if (e.keyCode === 27 && typeof handleClose === 'function') {
            handleClose(e);
        }
    }, []);

    useEffect(() => {
        document.addEventListener('keydown', closeModal, false);

        return () => {
            document.removeEventListener('keydown', closeModal, false);
        };
    }, []);

    if (!isOpen) return null;

    return createPortal(
        <div className={styles.wrapper}>
            <div
                role="dialog"
                aria-label={title}
                className={styles.dialog}
            >
                {showHeader ? (
                    <div className={styles.header}>
                        {title}
                        {handleClose && (
                            <button
                                aria-label={__('Close dialog', 'give')}
                                className={styles.close}
                                onClick={handleClose}
                            >
                                <ExitIcon aria-label={__('Close dialog icon', 'give')} />
                            </button>
                        )}
                    </div>
                ) : (
                    <>
                        {handleClose && (
                            <div className={styles.header2}>
                                <button
                                    aria-label={__('Close dialog', 'give')}
                                    className={styles.close}
                                    onClick={handleClose}
                                >
                                    <ExitIcon aria-label={__('Close dialog icon', 'give')} />
                                </button>
                            </div>
                        )}
                    </>
                )}

                <div className={styles.content}>
                    {children}
                </div>
            </div>
        </div>,
        insertInto ? document.querySelector(insertInto) : document.body
    );
}

