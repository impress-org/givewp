import {useEffect, useRef, useState} from '@wordpress/element';
import {createPortal} from 'react-dom';
import IframeResizer from 'iframe-resizer-react';

/**
 * @since 3.0.0
 */
export default function ModalForm({dataSrc, embedId, openFormButton}) {
    const [isOpen, setIsOpen] = useState(false);
    const modalRef = useRef(null);

    const toggleModal = () => {
        setIsOpen(!isOpen);
    };

    useEffect(() => {
        const {current: el} = modalRef;
        if (isOpen) el.showModal();
    }, [isOpen]);

    return (
        <>
            <button className={'givewp-donation-form-display__button'} onClick={toggleModal}>
                {openFormButton}
            </button>
            {isOpen &&
                createPortal(
                    <dialog className={'givewp-donation-form-modal'} ref={modalRef}>
                        <div className={'givewp-donation-form-modal__close'} onClick={toggleModal}>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M18.7071 6.70711C19.0976 6.31658 19.0976 5.68342 18.7071 5.29289C18.3166 4.90237 17.6834 4.90237 17.2929 5.29289L12 10.5858L6.70711 5.29289C6.31658 4.90237 5.68342 4.90237 5.29289 5.29289C4.90237 5.68342 4.90237 6.31658 5.29289 6.70711L10.5858 12L5.29289 17.2929C4.90237 17.6834 4.90237 18.3166 5.29289 18.7071C5.68342 19.0976 6.31658 19.0976 6.70711 18.7071L12 13.4142L17.2929 18.7071C17.6834 19.0976 18.3166 19.0976 18.7071 18.7071C19.0976 18.3166 19.0976 17.6834 18.7071 17.2929L13.4142 12L18.7071 6.70711Z"
                                    fill="black"
                                />
                            </svg>
                        </div>
                        <IframeResizer
                            id={embedId}
                            src={dataSrc}
                            checkOrigin={false}
                            style={{
                                width: '1px',
                                minWidth: '100%',
                                border: 'none',
                                overflowY: 'scroll',
                                background: 'none !important',
                            }}
                        />
                    </dialog>,
                    document.body
                )}
        </>
    );
}
