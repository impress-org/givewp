import {useEffect, useRef, useState} from '@wordpress/element';
import IframeResizer from 'iframe-resizer-react';
import {createPortal} from 'react-dom';

import '../../editor/styles/index.scss';
import {useCallback} from 'react';

type ModalFormProps = {
    dataSrc: string;
    embedId: string;
    openFormButton: string;
    openByDefault?: boolean;
    isFormRedirect: boolean;
    formViewUrl: string;
};

/**
 * @unreleased
 * @since 3.2.0 include types. update BEM classnames.
 * @since 3.0.0
 */
export default function ModalForm({dataSrc, embedId, openFormButton, openByDefault, isFormRedirect, formViewUrl}: ModalFormProps) {
    const [isOpen, setIsOpen] = useState(openByDefault || isFormRedirect);
    const modalRef = useRef(null);
    const [dataSrcUrl, setDataSrcUrl] = useState(dataSrc);

    const resetDataSrcUrl = () => {
         if (!isOpen && isFormRedirect) {
            setDataSrcUrl(formViewUrl);
        }
    };

    const toggleModal = () => {
        setIsOpen(!isOpen);

        resetDataSrcUrl();
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
                                className="givewp-donation-form-modal__close__icon"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                width="24"
                                height="24"
                                aria-hidden="true"
                                focusable="false"
                            >
                                <path
                                    stroke="black"
                                    strokeWidth="2"
                                    d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"
                                ></path>
                            </svg>
                        </button>
                        <IframeResizer
                            id={embedId}
                            src={dataSrcUrl}
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
