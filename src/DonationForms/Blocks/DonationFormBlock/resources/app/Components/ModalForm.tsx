import {useEffect, useRef, useState} from '@wordpress/element';
import IframeResizer from 'iframe-resizer-react';
import {createPortal} from 'react-dom';
import getWindowData from '@givewp/forms/app/utilities/getWindowData';

import '../../editor/styles/index.scss';
import isRouteInlineRedirect from '@givewp/forms/app/utilities/isRouteInlineRedirect';

type ModalFormProps = {
    dataSrc: string;
    embedId: string;
    openFormButton: string;
};

/**
 * @unreleased
 */
const inlineRedirectRoutes = ['donation-confirmation-receipt-view'];

/**
 * @since 3.2.0 include types. update BEM classnames.
 * @since 3.0.0
 */
export default function ModalForm({dataSrc, embedId, openFormButton}: ModalFormProps) {
    const redirectUrl = new URL(dataSrc);
    const redirectUrlParams = new URLSearchParams(redirectUrl.search);
    const shouldRedirectInline = isRouteInlineRedirect(redirectUrlParams, inlineRedirectRoutes);
    const [isOpen, setIsOpen] = useState(shouldRedirectInline);
    const modalRef = useRef(null);

    const toggleModal = () => {
        setIsOpen(!isOpen);
    };

    useEffect(() => {
        const {current: el} = modalRef;
        if (isOpen) {
            el.showModal();
        }
    }, [isOpen]);

    return (
        <div className={'givewp-donation-form-modal'}>
            <button className={'givewp-donation-form-modal__open'} onClick={toggleModal}>
                {openFormButton}
            </button>
            {isOpen &&
                createPortal(
                    <dialog className={'givewp-donation-form-modal__dialog'} ref={modalRef}>
                        <button
                            className="givewp-donation-form-modal__close"
                            type="button"
                            aria-label="Close"
                            onClick={toggleModal}
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                width="24"
                                height="24"
                                aria-hidden="true"
                                focusable="false"
                            >
                                <path d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"></path>
                            </svg>
                        </button>
                        <IframeResizer
                            id={embedId}
                            src={dataSrc}
                            checkOrigin={false}
                            style={{
                                width: '32.5rem',
                                minWidth: '100%',
                                border: 'none',
                                overflowY: 'scroll',
                                background: 'none !important',
                            }}
                        />
                    </dialog>,
                    document.body
                )}
        </div>
    );
}
