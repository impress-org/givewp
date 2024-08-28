import {SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import styles from './CampaignFormModal.module.scss';
import FormModal from '../FormModal';
import CampaignsApi from '../api';

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
    const offsetInMilliseconds = date.getTimezoneOffset() * 60 * 1000;
    const dateWithOffset = new Date(date.getTime() - offsetInMilliseconds);

    return removeTimezoneFromDateISOString(dateWithOffset.toISOString());
};

/**
 * Remove timezone from date string
 *
 * @unreleased
 */
const removeTimezoneFromDateISOString = (date: string) => {
    return date.slice(0, -5);
};

/**
 * Campaign Form Modal component
 *
 * @unreleased
 */
export default function CampaignFormModal({isOpen, handleClose, apiSettings, title, campaign}: CampaignModalProps) {
    const API = new CampaignsApi(apiSettings);

    const {
        register,
        handleSubmit,
        formState: {errors, isDirty},
    } = useForm<Inputs>({
        defaultValues: {
            title: campaign?.title ?? '',
            description: campaign?.description ?? '',
            startDateTime: getDateString(
                campaign?.startDateTime?.date ? new Date(campaign?.startDateTime?.date) : getNextSharpHour(1)
            ),
            endDateTime: getDateString(
                campaign?.endDateTime?.date ? new Date(campaign?.endDateTime?.date) : getNextSharpHour(2)
            ),
        },
    });

    const onSubmit: SubmitHandler<Inputs> = async (inputs) => {
        try {
            inputs.startDateTime = getDateString(new Date(inputs.startDateTime));
            inputs.endDateTime = getDateString(new Date(inputs.endDateTime));

            const endpoint = campaign?.id ? `/campaign/${campaign.id}` : '';
            const response = await API.fetchWithArgs(endpoint, inputs, 'POST');

            handleClose(response);
        } catch (error) {
            console.error('Error submitting campaign campaign', error);
        }
    };

    return (
        <FormModal
            isOpen={isOpen}
            handleClose={handleClose}
            title={title}
            handleSubmit={handleSubmit(onSubmit)}
            errors={errors}
            className={styles.campaignForm}
        >
            <div className="givewp-campaigns__form-row">
                <label htmlFor="title">{__('Campaign Name', 'give')}</label>
                <input
                    type="text"
                    {...register('title', {required: __('The campaign must have a name!', 'give')})}
                    aria-invalid={errors.title ? 'true' : 'false'}
                    placeholder={__('Enter campaign name', 'give')}
                />
            </div>
            <div className="givewp-campaigns__form-row">
                <label htmlFor="description">{__('Description', 'give')}</label>
                <textarea {...register('description')} rows={4} />
            </div>
            <div className="givewp-campaigns__form-row givewp-campaigns__form-row--half">
                <div className="givewp-campaigns__form-column">
                    <label htmlFor="startDateTime">{__('Start date and time', 'give')}</label>
                    <input
                        type="datetime-local"
                        {...register('startDateTime', {required: __('The campaign must have a start date!', 'give')})}
                        aria-invalid={errors.startDateTime ? 'true' : 'false'}
                    />
                </div>
                <div className="givewp-campaigns__form-column">
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
                {campaign?.id ? __('Save changes', 'give') : __('Save campaign', 'give')}
            </button>
        </FormModal>
    );
}

type Campaign = {
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

interface CampaignModalProps {
    isOpen: boolean;
    handleClose: (response?: any) => void;
    apiSettings: {
        apiRoot: string;
        apiNonce: string;
    };
    title: string;
    campaign?: Campaign;
}
