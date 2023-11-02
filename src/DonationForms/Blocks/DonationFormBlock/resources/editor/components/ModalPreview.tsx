import {useState} from '@wordpress/element';
import {createPortal} from 'react-dom';
import IframeResizer from 'iframe-resizer-react';
import {ModalToggle} from './ModalToggle';
import close from '../images/close-icon.svg';

import '../styles/index.scss';

type ModalPreviewProps = {
    enableIframe: 'auto' | 'none';
    formId: number;
    openFormButton: string;
};

/**
 * @unreleased updated BEM classnames and included button component.
 * @since 3.0.0
 */
export default function ModalPreview({enableIframe, formId, openFormButton}: ModalPreviewProps) {
    const [isOpen, setIsOpen] = useState(false);

    const toggleModal = () => {
        setIsOpen(!isOpen);
    };

    return (
        <div className={'givewp-donation-form-modal'}>
            <ModalToggle classname={'givewp-donation-form-modal__open'} onClick={toggleModal}>
                {openFormButton}
            </ModalToggle>
            {isOpen &&
                createPortal(
                    <dialog className={'givewp-donation-form-modal__dialog'} open={true}>
                        <ModalToggle classname={'givewp-donation-form-modal__close'} onClick={toggleModal}>
                            <img src={close} alt={'dismiss'} />
                        </ModalToggle>
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
        </div>
    );
}
