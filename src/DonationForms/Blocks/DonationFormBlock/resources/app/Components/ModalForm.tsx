import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import IframeResizer from 'iframe-resizer-react';
import FormModal from '../../common/FormModal';

import '../../editor/styles/index.scss';

type ModalFormProps = {
    dataSrc: string;
    embedId: string;
    openFormButton: string;
    isFormRedirect: boolean;
    formViewUrl: string;
};

/**
 * @since 3.6.1
 * @since 3.4.0
 * @since 3.2.0 include types. update BEM classnames.
 * @since 3.0.0
 */
export default function ModalForm({dataSrc, embedId, openFormButton, isFormRedirect, formViewUrl}: ModalFormProps) {
    const [dataSrcUrl, setDataSrcUrl] = useState(dataSrc);
    const [isOpen, setIsOpen] = useState<boolean>(isFormRedirect);

    // Offline gateways like Stripe refresh the page and need to programmatically
    // open the confirmation page from the modal.

    const resetDataSrcUrl = () => {
        if (!isOpen && isFormRedirect) {
            setDataSrcUrl(formViewUrl);
        }
    };

    const toggleModal = () => {
        setIsOpen(!isOpen);
        resetDataSrcUrl();
    };

    return (
        <FormModal isOpen={isOpen} onChange={toggleModal} openFormButton={openFormButton}>
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
            />
        </FormModal>
    );
}
