import {SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import ListTableApi from '@givewp/components/ListTable/api';
import styles from './TicketTypeFormModal.module.scss';
import FormModal from '../FormModal';
import {useTicketTypeForm} from './ticketTypeFormContext';
import {Inputs, TicketModalProps} from './types';
import {useEffect} from 'react';

/**
 * Ticket Form Modal component
 *
 * @unreleased
 */
export default function TicketTypeFormModal({isOpen, handleClose, apiSettings}: TicketModalProps) {
    const {ticketData} = useTicketTypeForm();
    const API = new ListTableApi(apiSettings);

    const {
        register,
        handleSubmit,
        reset,
        formState: {errors, isDirty},
    } = useForm<Inputs>();

    useEffect(() => {
        reset({
            title: ticketData?.title,
            description: ticketData?.description,
            price: ticketData?.price,
            capacity: ticketData?.capacity
        });
    }, [ticketData, reset]);

    const onSubmit: SubmitHandler<Inputs> = async (data) => {
        try {
            data.price = data.price * 100;

            const endpoint = ticketData?.id ? `/ticket-type/${ticketData.id}` : `/event/${apiSettings?.event?.id}/ticket-types`;
            const response = await API.fetchWithArgs(endpoint, data, 'POST');
            handleClose(response);
        } catch (error) {
            console.error('Error submitting ticket data', error);
        }
    };

    const modalTitle = ticketData?.id ? __('Edit ticket', 'give') : __('Create ticket', 'give');

    return (
        <FormModal
            isOpen={isOpen}
            handleClose={handleClose}
            title={modalTitle}
            handleSubmit={handleSubmit(onSubmit)}
            errors={errors}
            className={styles.ticketTypeForm}
        >
            <div className="givewp-event-tickets__form-row">
                <label htmlFor="title">{__('Ticket Name', 'give')}</label>
                <input
                    type="text"
                    {...register('title', {required: __('The ticket must have a name!', 'give')})}
                    aria-invalid={errors.title ? 'true' : 'false'}
                    placeholder={__('Enter ticket name', 'give')}
                />
            </div>
            <div className="givewp-event-tickets__form-row">
                <label htmlFor="description">{__('Description', 'give')}</label>
                <textarea {...register('description')} rows={4} />
            </div>
            <div className="givewp-event-tickets__form-row givewp-event-tickets__form-row--half">
                <div className="givewp-event-tickets__form-column">
                    <label htmlFor="price">{__('Price', 'give')}</label>
                    <input type="number"{...register('price')} />
                </div>
                <div className="givewp-event-tickets__form-column">
                    <label htmlFor="capacity">{__('Capacity', 'give')}</label>
                    <input type="number" {...register('capacity')} />
                </div>
            </div>

            <button
                type="submit"
                className={`button button-primary ${!isDirty ? 'disabled' : ''}`}
                aria-disabled={!isDirty}
                disabled={!isDirty}
            >
                {ticketData?.id ? __('Save changes', 'give') : __('Save ticket', 'give')}
            </button>
        </FormModal>
    );
}
