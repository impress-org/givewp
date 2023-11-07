import {MouseEventHandler, useCallback, useEffect, useState} from 'react';
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
}

export default function Modal({title, icon, children, insertInto, handleClose}: ModalProps) {
    const [isOpen, setIsOpen] = useState(false);

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

    return createPortal(
        <div className="givewp-modal-wrapper">
            <div role="dialog" aria-label={title} className="givewp-modal-dialog">
                {title}

                <button
                    aria-label={__('Close dialog', 'give')}
                    className="givewp-modal-close-headless"
                    onClick={handleClose}
                >
                    <ExitIcon aria-label={__('Close dialog icon', 'give')} />
                </button>

                <div className="givewp-modal-content">{children}</div>
            </div>
        </div>,
        document.body
    );
}
