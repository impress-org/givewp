import {useState} from '@wordpress/element';
import IframeResizer from 'iframe-resizer-react';
import {Modal} from '@wordpress/components';

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
            <button className={'givewp-donation-form-modal__open'} onClick={toggleModal}>
                {openFormButton}
            </button>
            {isOpen && (
                <Modal title={''} onRequestClose={toggleModal}>
                    <IframeResizer
                        src={`/?givewp-route=donation-form-view&form-id=${formId}`}
                        checkOrigin={false}
                        style={{
                            width: '32.5rem',
                            minWidth: '100%',
                            border: 'none',
                            overflowY: 'scroll',
                            pointerEvents: enableIframe,
                        }}
                    />
                </Modal>
            )}
        </div>
    );
}
