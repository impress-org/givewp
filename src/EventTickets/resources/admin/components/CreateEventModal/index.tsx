import {useState} from 'react';
import {SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import ListTableApi from '@givewp/components/ListTable/api';
import styles from './CreateEventModal.module.scss';
import FormModal from '../FormModal';

/**
 * Auto open modal if the URL has the query parameter id as new
 *
 * @unreleased
 */
const autoOpenModal = () => {
    const queryParams = new URLSearchParams(window.location.search);
    const newParam = queryParams.get('new');

    return newParam === 'event';
};

/**
 * Get the next sharp hour in ISO format
 *
 * @unreleased
 */
const getNextSharpHour = (hoursToAdd: number) => {
    const now = new Date();
    const offsetInMs = now.getTimezoneOffset() * 60 * 1000;
    const nowWithOffset = new Date(now.getTime() - offsetInMs);

    nowWithOffset.setHours(nowWithOffset.getHours() + hoursToAdd, 0, 0, 0);

    return nowWithOffset.toISOString().slice(0, -5);
};

/**
 * Create Event Modal component
 *
 * @unreleased
 */
export default function CreateEventModal() {
    const [isOpen, setOpen] = useState<boolean>(autoOpenModal());
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
            <FormModal
                isOpen={isOpen}
                handleClose={closeModal}
                title={__('Create your event', 'give')}
                handleSubmit={handleSubmit(onSubmit)}
                errors={errors}
                className={styles.createEventForm}
            >
                <div className="givewp-event-tickets__form-row">
                    <label htmlFor="title">{__('Event Name', 'give')}</label>
                    <input
                        type="text"
                        {...register('title', {required: __('The event must have a name!', 'give')})}
                        aria-invalid={errors.title ? 'true' : 'false'}
                        placeholder={__('Enter event name', 'give')}
                    />
                </div>
                <div className="givewp-event-tickets__form-row">
                    <label htmlFor="description">{__('Description', 'give')}</label>
                    <textarea {...register('description')} rows={4} />
                </div>
                <div className="givewp-event-tickets__form-row givewp-event-tickets__form-row--half">
                    <div className="givewp-event-tickets__form-column">
                        <label htmlFor="startDateTime">{__('Start date and time', 'give')}</label>
                        <input
                            type="datetime-local"
                            defaultValue={getNextSharpHour(1)}
                            {...register('startDateTime', {required: __('The event must have a start date!', 'give')})}
                            aria-invalid={errors.startDateTime ? 'true' : 'false'}
                        />
                    </div>
                    <div className="givewp-event-tickets__form-column">
                        <label htmlFor="endDateTime">{__('End date and time', 'give')}</label>
                        <input type="datetime-local" defaultValue={getNextSharpHour(2)} {...register('endDateTime')} />
                    </div>
                </div>

                <button type="submit" className="button button-primary">
                    {__('Save event', 'give')}
                </button>
            </FormModal>
        </>
    );
}

type Inputs = {
    title: string;
    description: string;
    startDateTime: string;
    endDateTime: string;
}
