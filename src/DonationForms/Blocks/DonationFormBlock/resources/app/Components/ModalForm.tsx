import {useEffect, useRef, useState} from '@wordpress/element';
import {createPortal} from 'react-dom';
import IframeResizer from 'iframe-resizer-react';
import {ModalToggle} from '../../editor/components/ModalToggle';
import close from '../../editor/images/close-icon.svg';

import '../../editor/styles/index.scss';

type ModalFormProps = {
    dataSrc: string;
    embedId: string;
    openFormButton: string;
};

/**
 * @since 3.0.0
 */
export default function ModalForm({dataSrc, embedId, openFormButton}: ModalFormProps) {
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
        <div className={'givewp-donation-form-modal'}>
            <ModalToggle classname={'givewp-donation-form-modal__open'} onClick={toggleModal}>
                {openFormButton}
            </ModalToggle>
            {isOpen &&
                createPortal(
                    <dialog className={'givewp-donation-form-modal__dialog'} ref={modalRef}>
                        <ModalToggle classname={'givewp-donation-form-modal__close'} onClick={toggleModal}>
                            <img src={close} alt={'dismiss'} />
                        </ModalToggle>
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
        </div>
    );
}
