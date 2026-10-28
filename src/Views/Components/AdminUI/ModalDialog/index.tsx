import {MouseEventHandler, useCallback, useEffect} from 'react';
import {createPortal} from 'react-dom';
import {__} from '@wordpress/i18n';
import {useConstrainedTabbing, useFocusOnMount, useMergeRefs} from '@wordpress/compose';
import {ExitIcon} from '@givewp/components/AdminUI/Icons';
import './style.scss';

export interface ModalProps {
    children: JSX.Element | JSX.Element[];
    title: string;
    isOpen?: boolean;
    icon?: JSX.Element | JSX.Element[];
    insertInto?: string;
    handleClose?: MouseEventHandler;
    showHeader?: boolean;
    showCloseIcon?: boolean;
    wrapperClassName?: string;
}

/**
 * @since TBD Move focus into the dialog when it opens and keep Tab inside it.
 */
export default function Modal({
    title,
    icon,
    children,
    insertInto,
    handleClose,
    isOpen = true,
    showHeader = true,
    showCloseIcon = true,
    wrapperClassName = '',
}: ModalProps) {
    const dialogRef = useMergeRefs([useFocusOnMount(true), useConstrainedTabbing()]);

    // ESC key closes modal, but only while it is open
    const closeModal = useCallback(
        (e) => {
            if (isOpen && e.keyCode === 27 && typeof handleClose === 'function') {
                handleClose(e);
            }
        },
        [isOpen, handleClose]
    );

    useEffect(() => {
        document.addEventListener('keydown', closeModal, false);

        return () => {
            document.removeEventListener('keydown', closeModal, false);
        };
    }, [closeModal]);

    useEffect(() => {
        if (isOpen) {
            document.body.classList.add('modalDialog-open');
        } else {
            document.body.classList.remove('modalDialog-open');
        }

        return () => {
            document.body.classList.remove('modalDialog-open');
        };
    }, [isOpen]);

    if (!isOpen) return null;

    return createPortal(
        <div className={`givewp-modal-wrapper ${wrapperClassName}`}>
            <div
                ref={dialogRef}
                role="dialog"
                aria-modal="true"
                aria-label={title}
                tabIndex={-1}
                className="givewp-modal-dialog"
            >
                <div className={`givewp-modal-dialog-content`}>
                    {showHeader ? (
                        <div className="givewp-modal-header">
                            {icon && <div className="givewp-modal-icon-header">{icon}</div>}
                            {title}
                            {showCloseIcon && handleClose && (
                                <button
                                    aria-label={__('Close dialog', 'give')}
                                    className="givewp-modal-close"
                                    onClick={handleClose}
                                >
                                    <ExitIcon aria-label={__('Close dialog icon', 'give')} />
                                </button>
                            )}
                        </div>
                    ) : (
                        <>
                            {showCloseIcon && handleClose && (
                                <button
                                    aria-label={__('Close dialog', 'give')}
                                    className="givewp-modal-close-headless"
                                    onClick={handleClose}
                                >
                                    <ExitIcon aria-label={__('Close dialog icon', 'give')} />
                                </button>
                            )}
                            {icon && <div className="givewp-modal-icon-center">{icon}</div>}
                        </>
                    )}

                    <div className="givewp-modal-content">{children}</div>
                </div>
            </div>
        </div>,
        insertInto ? document.querySelector(insertInto) : document.body
    );
}
