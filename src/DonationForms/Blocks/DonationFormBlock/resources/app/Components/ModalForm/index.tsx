import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import IframeResizer from 'iframe-resizer-react';
import {Button, Dialog, Modal} from 'react-aria-components';
import ModalCloseIcon from './ModalClose';
import {Spinner} from '@wordpress/components';
import './styles.scss';

import '../../../editor/styles/index.scss';
import {useCallback} from 'react';

type ModalFormProps = {
    dataSrc: string;
    embedId: string;
    openFormButton: string;
    isFormRedirect: boolean;
    formViewUrl: string;
};

/**
 * @since 4.0.0 updated to include loading state
 * @since 3.6.1
 * @since 3.4.0
 * @since 3.2.0 include types. update BEM classnames.
 * @since 3.0.0
 */
export default function ModalForm({dataSrc, embedId, openFormButton, isFormRedirect, formViewUrl}: ModalFormProps) {
    const [dataSrcUrl, setDataSrcUrl] = useState(dataSrc);
    const [isOpen, setIsOpen] = useState<boolean>(isFormRedirect);
    const [isLoading, setLoading] = useState<boolean>(false);

    // Offline gateways like Stripe refresh the page and need to programmatically
    // open the confirmation page from the modal.

    const resetDataSrcUrl = () => {
        if (!isOpen && isFormRedirect) {
            setDataSrcUrl(formViewUrl);
        }
    };

    const openModal = () => {
        setIsOpen(true);
        setLoading(true);
        resetDataSrcUrl();
    };

    const closeModal = () => {
        setIsOpen(false);
        setLoading(false);
        resetDataSrcUrl();
    };

    const Form = useCallback(
        () => (
            <IframeResizer
                title={__('Donation Form', 'give')}
                id={embedId}
                src={dataSrcUrl}
                checkOrigin={false}
                heightCalculationMethod={'taggedElement'}
                style={{
                    minWidth: '100%',
                    border: 'none',
                }}
                onInit={(iframe) => {
                    iframe.iFrameResizer.resize();
                    setLoading(false);
                }}
            />
        ),
        [dataSrcUrl, embedId]
    );

    return (
        <>
            <Button
                type="button"
                className="givewp-donation-form-modal__open"
                onPress={openModal}
                isDisabled={isLoading || isOpen}
            >
                <span>{isLoading ? <Spinner style={{margin: '0 auto'}} /> : openFormButton}</span>
            </Button>

            {isLoading && <div style={{display: 'none'}}>{<Form />}</div>}

            <Modal className="givewp-donation-form-modal__overlay" isOpen={isOpen && !isLoading}>
                <Dialog className="givewp-donation-form-modal__dialog">
                    <div className="givewp-donation-form-modal__dialog__content">
                        <Form />

                        <Button type="button" className="givewp-donation-form-modal__close" onPress={closeModal}>
                            <ModalCloseIcon />
                        </Button>
                    </div>
                </Dialog>
            </Modal>
        </>
    );
}
