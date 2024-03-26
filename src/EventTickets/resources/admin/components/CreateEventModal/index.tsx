import {useState} from 'react';
import {__} from '@wordpress/i18n';
import styles from './CreateEventModal.module.scss';
import EventFormModal from '../EventFormModal';

/**
 * Auto open modal if the URL has the query parameter id as new
 *
 * @since 3.6.0
 */
const autoOpenModal = () => {
    const queryParams = new URLSearchParams(window.location.search);
    const newParam = queryParams.get('new');

    return newParam === 'event';
};

/**
 * Create Event Modal component
 *
 * @since 3.6.0
 */
export default function CreateEventModal() {
    const [isOpen, setOpen] = useState<boolean>(autoOpenModal());
    const openModal = () => setOpen(true);
    const closeModal = (response: ResponseProps = {}) => {
        setOpen(false);

        if (response?.id) {
            window.location.href =
                window.GiveEventTickets.adminUrl +
                'edit.php?post_type=give_forms&page=give-event-tickets&id=' +
                response?.id;
        }
    };

    const apiSettings = window.GiveEventTickets;
    // Remove the /list-table from the apiRoot. This is a hack to make the API work while we don't refactor other list tables.
    apiSettings.apiRoot = apiSettings.apiRoot.replace('/list-table', '');

    return (
        <>
            <a className={`button button-primary ${styles.createEventButton}`} onClick={openModal}>
                {__('Create event', 'give')}
            </a>
            <EventFormModal
                isOpen={isOpen}
                handleClose={closeModal}
                title={__('Create your event', 'give')}
                apiSettings={apiSettings}
            />
        </>
    );
}

type ResponseProps = {
    id?: string;
};
