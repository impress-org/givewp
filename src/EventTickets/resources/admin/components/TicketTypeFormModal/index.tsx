import {Controller, SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import styles from './TicketTypeFormModal.module.scss';
import FormModal from '../FormModal';
import {useTicketTypeForm} from './ticketTypeFormContext';
import {Inputs, TicketModalProps} from './types';
import {useEffect} from 'react';
import EventTicketsApi from '../api';
import CurrencyInput from 'react-currency-input-field';
import parseValueFromLocale from './parseValueFromLocale';

/**
 * Ticket Form Modal component
 *
 * @since 3.20.0 Replace number input with CurrencyInput component
 * @since 3.6.0
 */
export default function TicketTypeFormModal({isOpen, handleClose, apiSettings, eventId}: TicketModalProps) {
    const {ticketData} = useTicketTypeForm();
    const API = new EventTicketsApi(apiSettings);

    const {
        register,
        control,
        handleSubmit,
        reset,
        formState: {errors, isDirty},
    } = useForm<Inputs>();

    useEffect(() => {
        reset({
            title: ticketData?.title || '',
            description: ticketData?.description || '',
            price: ticketData?.price || null,
            capacity: ticketData?.capacity || 50,
        });
    }, [ticketData, reset]);

    const onSubmit: SubmitHandler<Inputs> = async (data) => {
        try {
            data.price = data.price * 100;
            data.capacity = data.capacity || 0;

            const endpoint = ticketData?.id ? `/ticket-type/${ticketData.id}` : `/event/${eventId}/ticket-types`;
            const response = await API.fetchWithArgs(endpoint, data, 'POST');

            handleClose(response);
            reset();
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
                    {...register('title', {required: __('The ticket name is required.', 'give')})}
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
                    <Controller
                        name="price"
                        control={control}
                        render={({field: {onChange, value: fieldValue, ref}}) => (
                            <CurrencyInput
                                intlConfig={{
                                    locale: navigator.language,
                                    currency: apiSettings.currencyCode,
                                }}
                                ref={ref}
                                id="price"
                                name="price"
                                decimalsLimit={2}
                                value={fieldValue}
                                onValueChange={(value) =>
                                    onChange(parseInt(value) >= 0 ? parseValueFromLocale(value) : '')
                                }
                            />
                        )}
                    />
                    <span>
                        {__('Leave empty for', 'give')}&nbsp;
                        <strong>{__('free', 'give')}</strong>
                    </span>
                </div>
                <div className="givewp-event-tickets__form-column">
                    <label htmlFor="capacity">{__('Capacity', 'give')}</label>
                    <input
                        type="number"
                        {...register('capacity', {required: __('The ticket capacity is required.', 'give')})}
                    />
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
