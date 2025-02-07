import {SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import styles from './EventFormModal.module.scss';
import FormModal from '../FormModal';
import EventTicketsApi from '../api';

/**
 * Get the next sharp hour
 *
 * @since 3.6.0
 */
const getNextSharpHour = (hoursToAdd: number) => {
    const date = new Date();
    date.setHours(date.getHours() + hoursToAdd, 0, 0, 0);

    return date;
};

/**
 * Format a given date to be used in datetime inputs
 *
 * @since 3.6.0
 */
const getDateString = (date: Date) => {
    const offsetInMilliseconds = date.getTimezoneOffset() * 60 * 1000;
    const dateWithOffset = new Date(date.getTime() - offsetInMilliseconds);

    return removeTimezoneFromDateISOString(dateWithOffset.toISOString());
};

/**
 * Remove timezone from date string
 *
 * @since 3.6.0
 */
const removeTimezoneFromDateISOString = (date: string) => {
    return date.slice(0, -5);
};

/**
 * Event Form Modal component
 *
 * @since 3.20.0 Added placeholder to event description field
 * @since 3.6.0
 */
export default function EventFormModal({isOpen, handleClose, apiSettings, title, event}: EventModalProps) {
    const API = new EventTicketsApi(apiSettings);

    const {
        register,
        handleSubmit,
        formState: {errors, isDirty},
    } = useForm<Inputs>({
        defaultValues: {
            title: event?.title ?? '',
            description: event?.description ?? '',
            startDateTime: getDateString(
                event?.startDateTime?.date ? new Date(event?.startDateTime?.date) : getNextSharpHour(1)
            ),
            endDateTime: getDateString(
                event?.endDateTime?.date ? new Date(event?.endDateTime?.date) : getNextSharpHour(2)
            ),
        },
    });

    const onSubmit: SubmitHandler<Inputs> = async (inputs) => {
        try {
            inputs.startDateTime = getDateString(new Date(inputs.startDateTime));
            inputs.endDateTime = getDateString(new Date(inputs.endDateTime));

            const endpoint = event?.id ? `/event/${event.id}` : '';
            const response = await API.fetchWithArgs(endpoint, inputs, 'POST');

            handleClose(response);
        } catch (error) {
            console.error('Error submitting event event', error);
        }
    };

    return (
        <FormModal
            isOpen={isOpen}
            handleClose={handleClose}
            title={title}
            handleSubmit={handleSubmit(onSubmit)}
            errors={errors}
            className={styles.eventForm}
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
                <textarea
                    {...register('description')}
                    rows={4}
                    placeholder={__('Briefly describe the details of your event', 'give')}
                />
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
    startDateTime: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    endDateTime: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    createdAt: string;
    updatedAt: string;
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
