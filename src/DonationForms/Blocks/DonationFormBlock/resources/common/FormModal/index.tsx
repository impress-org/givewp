import {Button, Dialog, Modal} from 'react-aria-components';
import './styles.scss';
import ModalCloseIcon from './ModalClose';
import {Spinner} from '@wordpress/components';

type FormModalProps = {
    children: any;
    openFormButton: string;
    isOpen: boolean;
    onChange?: () => void;
    isLoading?: boolean;
};

/**
 * @since 4.0.0 updated to include loading state
 * @since 3.6.1
 */
export default function FormModal({openFormButton, children, onChange, isOpen, isLoading}: FormModalProps) {
    return (
        <>
            <Button className={'givewp-donation-form-modal__open'} onPress={onChange} isDisabled={isLoading || isOpen}>
                <span>{isLoading ? <Spinner /> : openFormButton}</span>
            </Button>
            {isLoading && <div style={{display: 'none'}}>{children}</div>}
            <Modal className='givewp-donation-form-modal__overlay' isOpen={isOpen && !isLoading}>
                <Dialog className='givewp-donation-form-modal__dialog'>
                    <div className="givewp-donation-form-modal__dialog__content">
                        {children}

                        <Button className="givewp-donation-form-modal__close" onPress={onChange}>
                            <ModalCloseIcon />
                        </Button>
                    </div>
                </Dialog>
            </Modal>
        </>
    );
}
