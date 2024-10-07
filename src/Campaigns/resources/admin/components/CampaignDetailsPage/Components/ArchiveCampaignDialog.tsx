import {__} from '@wordpress/i18n'
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {ErrorIcon} from '../Icons';

/**
 * @unreleased
 */
export default ({
    isOpen,
    title,
    handleClose,
    handleConfirm,
    className,
}: {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: () => void;
    title: string;
    className?: string;
}) => {
    return (
        <ModalDialog
            icon={<ErrorIcon />}
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={title}
            wrapperClassName={className}
        >
            <>
                <div>{__('Are you sure you want to archive your campaign? All forms associated with this campaign will be inaccessible to donors.', 'give')}</div>
                <div>
                    <button onClick={handleConfirm}>submit</button>
                </div>
            </>
        </ModalDialog>
    );
}
