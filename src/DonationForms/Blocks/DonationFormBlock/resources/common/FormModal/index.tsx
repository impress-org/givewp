import {Button, Dialog, Modal} from 'react-aria-components';
import './styles.scss';
import ModalCloseIcon from './ModalClose';

type FormModalProps = {
    children: any;
    openFormButton: string;
    isOpen: boolean;
    onChange?: () => void;
};

/**
 * @since 3.6.1
 */
export default function FormModal({openFormButton, children, onChange, isOpen}: FormModalProps) {
    return (
        <>
            <Button className={'givewp-donation-form-modal__open'} onPress={onChange}>
                {openFormButton}
            </Button>
            <Modal className={'givewp-donation-form-modal__overlay'} isOpen={isOpen}>
                <Dialog className={'givewp-donation-form-modal__dialog'}>
                    {children}
                    <Button className="givewp-donation-form-modal__close" onPress={onChange}>
                        <ModalCloseIcon />
                    </Button>
                </Dialog>
            </Modal>
        </>
    );
}
