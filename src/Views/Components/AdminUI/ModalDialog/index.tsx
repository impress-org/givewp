import {MouseEventHandler, useCallback, useEffect} from 'react';
import {createPortal} from 'react-dom';
import {__} from '@wordpress/i18n';
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
    // ESC key closes modal
    const closeModal = useCallback((e) => {
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
        <div className={`givewp-modal-wrapper ${wrapperClassName}`}>
            <div role="dialog" aria-label={title} className="givewp-modal-dialog">
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
        </div>,
        insertInto ? document.querySelector(insertInto) : document.body
    );
}

