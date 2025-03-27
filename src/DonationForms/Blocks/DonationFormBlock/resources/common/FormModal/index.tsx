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
 * @unreleased updated to include loading state
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
                    {isLoading && <FormPreviewLoading design={formDesign} />}
                    {/*<div className='givewp-donation-form-modal__spinner' style={{visibility: isLoading ? 'visible' : 'hidden', opacity: isLoading ? 1 : 0, height: isLoading ? '80vh' : 0}}>*/}
                    {/*   <Spinner />*/}
                    {/*</div>*/}
                    <div className="givewp-donation-form-modal__dialog__content" style={{visibility: isLoading ? 'hidden' : 'visible', opacity: isLoading ? 0 : 1}}>
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
