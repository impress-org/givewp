import {useEffect, useRef, useState} from '@wordpress/element';
import IframeResizer from 'iframe-resizer-react';
import {ModalToggle} from '../../editor/components/ModalToggle';
import {Modal} from '@wordpress/components';
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
            {isOpen && (
                <Modal title={''} onRequestClose={toggleModal}>
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
                </Modal>
            )}
        </div>
    );
}
