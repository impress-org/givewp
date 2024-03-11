import {Button, Dialog, DialogTrigger, Modal as ModalOverlay} from 'react-aria-components';
import './styles.scss';
import ModalCloseIcon from './ModalClose';

type FormModalProps = {
    children: any;
    openFormButton: string;
    onChange?: () => void;
};

/**
 * @unreleased
 */
export default function FormModal({openFormButton, children, onChange}: FormModalProps) {
    return (
        <>
            <DialogTrigger onOpenChange={onChange}>
                <Button className={'givewp-donation-form-modal__open'}>{openFormButton}</Button>
                <ModalOverlay className={'givewp-donation-form-modal__overlay'}>
                    <Dialog className={'givewp-donation-form-modal__dialog'}>
                        {({close}) => (
                            <>
                                {children}
                                <Button className="givewp-donation-form-modal__close" onPress={close}>
                                    <ModalCloseIcon />
                                </Button>
                            </>
                        )}
                    </Dialog>
                </ModalOverlay>
            </DialogTrigger>
        </>
    );
}
