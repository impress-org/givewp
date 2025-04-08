import {Button, Dialog, Modal} from 'react-aria-components';
import './styles.scss';
import ModalCloseIcon from './ModalClose';
import FormPreviewLoading from '../FormPreviewLoading';

type FormModalProps = {
    children: any;
    openFormButton: string;
    isOpen: boolean;
    onChange?: () => void;
    isLoading?: boolean;
    formDesign?: string;
};

/**
 * @since 4.0.0 updated to include loading state
 * @since 3.6.1
 */
export default function FormModal({openFormButton, children, onChange, isOpen, isLoading, formDesign}: FormModalProps) {
    return (
        <>
            <Button className={'givewp-donation-form-modal__open'} onPress={onChange}>
                {openFormButton}
            </Button>
            <Modal className='givewp-donation-form-modal__overlay' isOpen={isOpen}>
                <Dialog className='givewp-donation-form-modal__dialog'>
                    <Button className="givewp-donation-form-modal__close" onPress={onChange}>
                        <ModalCloseIcon />
                    </Button>
                    <div className="givewp-donation-form-modal__dialog__content" style={{visibility: isLoading ? 'hidden' : 'visible', opacity: isLoading ? 0 : 1}}>
                        {children}
                    </div>
                    <FormPreviewLoading design={formDesign} isLoading={isLoading} />
                </Dialog>
            </Modal>
        </>
    );
}
