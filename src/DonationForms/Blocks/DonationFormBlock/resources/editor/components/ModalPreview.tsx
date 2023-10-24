import {useState} from '@wordpress/element';
import {createPortal} from 'react-dom';
import IframeResizer from 'iframe-resizer-react';

/**
 * @since 3.0.0
 */
export default function ModalPreview({enableIframe, formId, openFormButton}) {
    const [isOpen, setIsOpen] = useState(false);

    const toggleModal = () => {
        setIsOpen(!isOpen);
    };

    return (
        <>
            <button onClick={toggleModal} className={'givewp-form-block__display-button'}>
                {openFormButton}
            </button>
            {isOpen &&
                createPortal(
                    <dialog className={'givewp-donation-form-modal'} open={true}>
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
                            src={`/?givewp-route=donation-form-view&form-id=${formId}`}
                            checkOrigin={false}
                            style={{
                                width: '1px',
                                minWidth: '100%',
                                border: 'none',
                                overflowY: 'scroll',
                                background: 'none !important',
                                pointerEvents: enableIframe,
                            }}
                        />
                    </dialog>,
                    document.body
                )}
        </>
    );
}
