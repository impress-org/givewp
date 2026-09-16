import {useEffect, useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import IframeResizer from 'iframe-resizer-react';
import {Button, Dialog, Modal, ModalOverlay} from 'react-aria-components';
import ModalCloseIcon from './ModalClose';
import './styles.scss';
import '../EntitySelector/styles/index.scss';
import {FocusScope} from 'react-aria';

/**
 * @unreleasaed
 */
type ModalFormProps = {
    dataSrc: string;
    embedId: string;
    buttonText: string;
    isFormRedirect: boolean;
    formViewUrl: string;
};

/**
 * @since TBD Share the launcher's pending state markup and styles with the external embed.
 * @unreleasaed
 */
export default function ModalForm({dataSrc, embedId, buttonText, isFormRedirect, formViewUrl}: ModalFormProps) {
    const [dataSrcUrl, setDataSrcUrl] = useState(dataSrc);
    const [isOpen, setIsOpen] = useState<boolean>(isFormRedirect);
    const [isLoading, setLoading] = useState<boolean>(false);
    const [isEntering, setEntering] = useState<boolean>(false);

    useEffect(() => {
        const handleEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape' && isOpen) {
                closeModal();
            }
        };

        document.addEventListener('keydown', handleEscape);
        return () => {
            document.removeEventListener('keydown', handleEscape);
        };
    }, [isOpen]);

    // Preload the iframe document
    useEffect(() => {
        const selector = `link[rel="preload"][href="${dataSrcUrl}"][as="document"]`;

        const existingLink = document.querySelector(selector);

        if (!existingLink) {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = dataSrcUrl;
            link.as = 'document';
            document.head.appendChild(link);
        }

        return () => {
            const addedLink = document.querySelector(selector);
            if (addedLink) {
                document.head.removeChild(addedLink);
            }
        };
    }, [dataSrcUrl]);

    useEffect(() => {
        if (isEntering && !isLoading) {
            resetEntering();
        }
    }, [isEntering, isLoading]);

    // Offline gateways like Stripe refresh the page and need to programmatically
    // open the confirmation page from the modal.
    const resetDataSrcUrl = () => {
        if (!isOpen && isFormRedirect) {
            setDataSrcUrl(formViewUrl);
        }
    };

    const openModal = () => {
        setEntering(true);
        setIsOpen(true);
        setLoading(true);
        resetDataSrcUrl();
    };

    const closeModal = () => {
        setIsOpen(false);
        setLoading(false);
        resetDataSrcUrl();
    };

    const resetEntering = () => {
        setTimeout(() => {
            setEntering(false);
        }, 2000);
    };

    return (
        <>
            <Button
                type="button"
                className="givewp-donation-form-modal__open"
                onPress={openModal}
                isPending={isLoading}
                aria-label={isLoading ? __('Loading donation form', 'give') : undefined}
            >
                {isLoading && <span className="givewp-donation-form-modal__open__spinner" aria-hidden="true" />}
                <span className="givewp-donation-form-modal__open__label">{buttonText}</span>
            </Button>

            <ModalOverlay
                className="givewp-donation-form-modal__overlay"
                data-loading={isLoading}
                isOpen={isOpen}
                onOpenChange={setIsOpen}
                isDismissable
                isEntering={isEntering}
            >
                <Modal className="givewp-donation-form-modal" data-loading={isLoading}>
                    <FocusScope contain restoreFocus autoFocus>
                        <Dialog
                            className="givewp-donation-form-modal__dialog"
                            aria-label={__('Donation Form', 'give')}
                            role="dialog"
                            aria-modal="true"
                        >
                            <button
                                aria-label={__('Close donation form', 'give')}
                                type="button"
                                className="givewp-donation-form-modal__close"
                                onClick={closeModal}
                                tabIndex={0}
                            >
                                <ModalCloseIcon />
                            </button>
                            <div className="givewp-donation-form-modal__dialog__content">
                                <IframeResizer
                                    title={__('Donation Form', 'give')}
                                    id={embedId}
                                    src={dataSrcUrl}
                                    checkOrigin={false}
                                    heightCalculationMethod="taggedElement"
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
                        </Dialog>
                    </FocusScope>
                </Modal>
            </ModalOverlay>
        </>
    );
}
