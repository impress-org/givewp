import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {store} from '../../tabs/recurring-donations/store';
import {setError} from '../../tabs/recurring-donations/store/actions';

import './style.scss';

type ErrorMessageProps = {
    error: string;
};

export default function ErrorMessage({error}: ErrorMessageProps) {
    const {dispatch} = store;

    const toggleModal = () => {
        dispatch(setError(null));
    };

    return (
        <ModalDialog
            wrapperClassName={'give-donor-dashboard__error-modal'}
            title={__('Error', 'give')}
            showHeader={true}
            isOpen={!!error}
            handleClose={toggleModal}
        >
            <p className={'give-donor-dashboard__error-message'}>{error}</p>
            <button className={'give-donor-dashboard__error-close'} onClick={toggleModal}>
                {__('Okay', 'give')}
            </button>
        </ModalDialog>
    );
}
