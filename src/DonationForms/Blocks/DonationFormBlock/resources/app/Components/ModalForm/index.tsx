import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import IframeResizer from 'iframe-resizer-react';
import {Button, Dialog, Modal} from 'react-aria-components';
import ModalCloseIcon from './ModalClose';
import FormPreviewLoading from '../FormPreviewLoading';
import './styles.scss';

import '../../../editor/styles/index.scss';

type ModalFormProps = {
    dataSrc: string;
    embedId: string;
    openFormButton: string;
    isFormRedirect: boolean;
    formViewUrl: string;
    formDesign?: string;
};

/**
 * @since 4.0.0 updated to include loading state
 * @since 3.6.1
 * @since 3.4.0
 * @since 3.2.0 include types. update BEM classnames.
 * @since 3.0.0
 */
export default function ModalForm({
    dataSrc,
    embedId,
    openFormButton,
    isFormRedirect,
    formViewUrl,
    formDesign,
}: ModalFormProps) {
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
    };

    const closeModal = () => {
        setIsOpen(false);
        setLoading(false);
        resetDataSrcUrl();
    };

    return (
        <>
            <Button className={'givewp-donation-form-modal__open'} onPress={openModal} isDisabled={isLoading || isOpen}>
                {openFormButton}
            </Button>

            <Modal className="givewp-donation-form-modal__overlay" isOpen={isOpen}>
                <Dialog className="givewp-donation-form-modal__dialog">
                    <Button className="givewp-donation-form-modal__close" onPress={closeModal}>
                        <ModalCloseIcon />
                    </Button>

                    <div
                        className="givewp-donation-form-modal__dialog__content"
                        style={{visibility: isLoading ? 'hidden' : 'visible', opacity: isLoading ? 0 : 1}}
                    >
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
                    </div>

                    <FormPreviewLoading design={formDesign} isLoading={isLoading} />
                </Dialog>
            </Modal>
        </>
    );
}
