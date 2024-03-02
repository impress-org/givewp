import {SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import ListTableApi from '@givewp/components/ListTable/api';
import styles from './EventFormModal.module.scss';
import FormModal from '../FormModal';

/**
 * Get the next sharp hour
 *
 * @unreleased
 */
const getNextSharpHour = (hoursToAdd: number) => {
    const date = new Date();
    date.setHours(date.getHours() + hoursToAdd, 0, 0, 0);

    return date;
};

/**
 * Format a given date to be used in datetime inputs
 *
 * @unreleased
 */
const getDateString = (date: Date) => {
    if (!date) {
        return;
    }

    const offsetInMs = date.getTimezoneOffset() * 60 * 1000;
    const dateWithOffset = new Date(date.getTime() - offsetInMs);

    return dateWithOffset.toISOString().slice(0, -8);
};

/**
 * Create Event Modal component
 *
 * @unreleased
 */
export default function EventFormModal({isOpen, handleClose, apiSettings, title, event}: EventModalProps) {
    const API = new ListTableApi(apiSettings);

    const {
        register,
        handleSubmit,
        formState: {errors, isDirty},
    } = useForm<Inputs>({
        defaultValues: {
            title: event?.title ?? '',
            description: event?.description ?? '',
            startDateTime: getDateString(event?.startDateTime ?? getNextSharpHour(1)),
            endDateTime: getDateString(event?.endDateTime ?? getNextSharpHour(2)),
        },
    });

    const onSubmit: SubmitHandler<Inputs> = async (data) => {
        try {
            data.startDateTime += ':00';
            data.endDateTime += ':00';

            const endpoint = event?.id ? `/event/${event.id}` : '';
            const response = await API.fetchWithArgs(endpoint, data, 'POST');
            handleClose(response);
        } catch (error) {
            console.error('Error submitting event data', error);
        }
    };

    return (
        <FormModal
            isOpen={isOpen}
            handleClose={handleClose}
            title={title}
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
                        {...register('startDateTime', {required: __('The event must have a start date!', 'give')})}
                        aria-invalid={errors.startDateTime ? 'true' : 'false'}
                    />
                </div>
                <div className="givewp-event-tickets__form-column">
                    <label htmlFor="endDateTime">{__('End date and time', 'give')}</label>
                    <input type="datetime-local" {...register('endDateTime')} />
                </div>
            </div>

            <button
                type="submit"
                className={`button button-primary ${!isDirty ? 'disabled' : ''}`}
                aria-disabled={!isDirty}
                disabled={!isDirty}
            >
                {event?.id ? __('Save changes', 'give') : __('Save event', 'give')}
            </button>
        </FormModal>
    );
}

type Event = {
    id?: number;
    title: string;
    description: string;
    startDateTime: Date;
    endDateTime: Date;
};

type Inputs = {
    title: string;
    description: string;
    startDateTime: string;
    endDateTime: string;
};

interface EventModalProps {
    isOpen: boolean;
    handleClose: (response?: any) => void;
    apiSettings: {
        apiRoot: string;
        apiNonce: string;
    };
    title: string;
    event?: Event;
}
