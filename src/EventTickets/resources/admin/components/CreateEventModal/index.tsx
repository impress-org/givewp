// Importing necessary hooks and SWR function
import {useState} from 'react';
import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import styles from './CreateEventModal.module.scss';
import {SubmitHandler, useForm} from 'react-hook-form';
import ListTableApi from '@givewp/components/ListTable/api';

export default function CreateEventModal() {
    const [isOpen, setOpen] = useState(false);
    const openModal = () => setOpen(true);
    const closeModal = () => setOpen(false);

    const apiSettings = window.GiveEventTickets;
    // Remove the /list-table from the apiRoot. This is a hack to make the API work while we don't refactor other list tables.
    apiSettings.apiRoot = apiSettings.apiRoot.replace('/list-table', '');
    const API = new ListTableApi(apiSettings);

    // useForm hook for form management
    const {
        register,
        handleSubmit,
        watch,
        formState: {errors},
        reset,
    } = useForm<Inputs>();

    const onSubmit: SubmitHandler<Inputs> = async (data) => {
        try {
            data.startDateTime += ':00';
            data.endDateTime += ':00';

            const response = await API.fetchWithArgs('', data, 'POST');
            closeModal();
            reset();
            window.location.href =
                window.GiveEventTickets.adminUrl +
                'edit.php?post_type=give_forms&page=give-event-tickets&id=' +
                response.id;
        } catch (error) {
            console.error('Error submitting event data', error);
        }
    };

    return (
        <>
            <a className={`button button-primary ${styles.createEventButton}`} onClick={openModal}>
                {__('Create event', 'give')}
            </a>
            <ModalDialog
                isOpen={isOpen}
                showHeader={true}
                handleClose={closeModal}
                title={__('Create your event', 'give')}
            >
                <form className={styles.createEventForm} onSubmit={handleSubmit(onSubmit)}>
                    <div className={styles.formRow}>
                        <label htmlFor="event-name">{__('Event Name', 'give')}</label>
                        <input type="text" {...register('title', {required: true})} />
                    </div>
                    <div className={styles.formRow}>
                        <label htmlFor="event-description">{__('Description', 'give')}</label>
                        <textarea {...register('description')} />
                    </div>
                    <div className={styles.formRow}>
                        <div className={styles.formColumn}>
                            <label htmlFor="event-date">{__('Start date and time', 'give')}</label>
                            <input
                                type="datetime-local"
                                defaultValue={new Date().toISOString().substring(0, 16)}
                                {...register('startDateTime', {required: true})}
                            />
                        </div>
                        <div className={styles.formColumn}>
                            <label htmlFor="event-time">{__('End date and time', 'give')}</label>
                            <input
                                type="datetime-local"
                                defaultValue={new Date().toISOString().substring(0, 16)}
                                {...register('endDateTime')}
                            />
                        </div>
                    </div>

                    {errors.title && <span>{__('The event must have a name!', 'give')}</span>}
                    {errors.startDateTime && <span>{__('The event must have a start date!', 'give')}</span>}

                    <button type="submit" className={`button button-primary ${styles.submitButton}`}>
                        {__('Save event', 'give')}
                    </button>
                </form>
            </ModalDialog>
        </>
    );
}

type Inputs = {
    title: string;
    description: string;
    startDateTime: string;
    endDateTime: string;
}
