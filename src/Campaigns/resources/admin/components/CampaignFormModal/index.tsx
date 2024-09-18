import {SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import styles from './CampaignFormModal.module.scss';
import FormModal from '../FormModal';
import CampaignsApi from '../api';
import {CampaignFormInputs, CampaignModalProps} from './types';
import {useEffect, useState} from 'react';
import UploadCoverImage from './UploadCoverImage';
//import {upload} from '@wordpress/icons';

//console.log('upload: ', upload);

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
    const [formModalTitle, setFormModalTitle] = useState<string>(title);
    const [step, setStep] = useState<number>(1);

    useEffect(() => {
        switch (step) {
            case 1:
                setFormModalTitle(__('Tell us about your fundraising cause', 'give'));
                break;
            case 2:
                setFormModalTitle(__('Set up your campaign goal', 'give'));
                break;
        }
    }, [step]);

    const {
        register,
        handleSubmit,
        formState: {errors, isDirty, isSubmitting},
        setValue,
        watch,
        trigger,
    } = useForm<CampaignFormInputs>({
        defaultValues: {
            title: campaign?.title ?? '',
            shortDescription: campaign?.shortDescription ?? '',
            image: campaign?.image ?? '',
            startDateTime: getDateString(
                campaign?.startDateTime?.date ? new Date(campaign?.startDateTime?.date) : getNextSharpHour(1)
            ),
            endDateTime: getDateString(
                campaign?.endDateTime?.date ? new Date(campaign?.endDateTime?.date) : getNextSharpHour(2)
            ),
        },
    });

    const image = watch('image');

    const validateTitle = async () => {
        const isValid = await trigger('title');

        if (!isValid) {
            console.log('Title is invalid!');
        }

        return isValid;
    };

    const onSubmit: SubmitHandler<CampaignFormInputs> = async (inputs, event) => {
        event.preventDefault();

        if (step !== 2) {
            return;
        }

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

    const requiredAsterisk = <span className={`givewp-field-required ${styles.fieldRequired}`}>*</span>;

    const Step1 = () => {
        return (
            <>
                <div className="givewp-campaigns__form-row">
                    <label htmlFor="title">
                        {__("What's the title of your campaign?", 'give')} {requiredAsterisk}
                    </label>
                    <span className={styles.description}>
                        {__("Give your campaign a title that tells donors what it's about.", 'give')}
                    </span>
                    <input
                        type="text"
                        {...register('title', {required: __('The campaign must have a title!', 'give')})}
                        aria-invalid={errors.title ? 'true' : 'false'}
                        placeholder={__('Eg. Holiday Food Drive', 'give')}
                        onBlur={validateTitle}
                    />
                    {errors.title && (
                        <div className={'givewp-campaigns__form-errors'}>
                            <p>{errors.title.message}</p>
                        </div>
                    )}
                </div>
                <div className="givewp-campaigns__form-row">
                    <label htmlFor="shortDescription">{__("What's your campaign about?", 'give')}</label>
                    <span className={styles.description}>
                        {__('Let your donors know the story behind your campaign.', 'give')}
                    </span>
                    <textarea
                        {...register('shortDescription')}
                        rows={4}
                        placeholder={__(
                            'Every family deserves a home-cooked holiday meal. Our organization collects non-perishable food and monetary donations each year to deliver holiday meal boxes to dozens of families in need from our own community.',
                            'give'
                        )}
                    />
                </div>
                <div className="givewp-campaigns__form-row">
                    <label htmlFor="shortDescription">
                        {__('Add a cover image or video for your campaign.', 'give')}
                    </label>
                    <span className={styles.description}>
                        {__('Upload an image or video to represent and inspire your campaign.', 'give')}
                    </span>
                    <UploadCoverImage
                        id="givewp-campaigns-upload-cover-image"
                        label={__('Image', 'give')}
                        actionLabel={__('Select to upload', 'give')}
                        value={image}
                        onChange={(coverImageUrl, coverImageAlt) => {
                            console.log('coverImageUrl: ', coverImageUrl);
                            setValue('image', coverImageUrl);
                        }}
                        reset={() => setValue('image', '')}
                    />
                </div>
                <button
                    type="submit"
                    onClick={async () => (await validateTitle()) && setStep(2)}
                    className={`button button-primary ${!isDirty ? 'disabled' : ''}`}
                    aria-disabled={!isDirty}
                    disabled={!isDirty}
                >
                    {__('Continue', 'give')}
                </button>
            </>
        );
    };

    const Step2 = () => {
        return (
            <>
                <div className="givewp-campaigns__form-row givewp-campaigns__form-row--half">
                    <div className="givewp-campaigns__form-column">
                        <label htmlFor="startDateTime">{__('Start date and time', 'give')}</label>
                        <input
                            type="datetime-local"
                            {...register('startDateTime', {
                                required: __('The campaign must have a start date!', 'give'),
                            })}
                            aria-invalid={errors.startDateTime ? 'true' : 'false'}
                        />
                    </div>
                    <div className="givewp-campaigns__form-column">
                        <label htmlFor="endDateTime">{__('End date and time', 'give')}</label>
                        <input type="datetime-local" {...register('endDateTime')} />
                    </div>
                </div>

                <div className="givewp-campaigns__form-row givewp-campaigns__form-row--half">
                    <button type="submit" onClick={() => setStep(1)} className={`button button-secondary`}>
                        {__('Previous', 'give')}
                    </button>

                    <button
                        type="submit"
                        className={`button button-primary ${!isDirty || isSubmitting ? 'disabled' : ''}`}
                        aria-disabled={!isDirty || isSubmitting}
                        disabled={!isDirty || isSubmitting}
                    >
                        {__('Continue', 'give')}
                    </button>
                </div>
            </>
        );
    };

    const FormSteps = () => {
        switch (step) {
            case 1:
                return <Step1 />;
            case 2:
                return <Step2 />;
        }
    };

    return (
        <FormModal
            isOpen={isOpen}
            handleClose={handleClose}
            title={formModalTitle}
            handleSubmit={handleSubmit(onSubmit)}
            errors={[]}
            className={styles.campaignForm}
        >
            {<FormSteps />}
        </FormModal>
    );
}
