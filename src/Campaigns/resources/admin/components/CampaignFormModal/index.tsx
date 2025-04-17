import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {__, sprintf} from '@wordpress/i18n';
import styles from './CampaignFormModal.module.scss';
import FormModal from '../FormModal';
import CampaignsApi from '../api';
import {CampaignFormInputs, CampaignModalProps, GoalTypeOption as GoalTypeOptionType} from './types';
import {useRef, useState} from 'react';
import {Currency, Upload} from '../Inputs';
import {
    AmountFromSubscriptionsIcon,
    AmountIcon,
    DonationsIcon,
    DonorsFromSubscriptionsIcon,
    DonorsIcon,
    SubscriptionsIcon,
} from './GoalTypeIcons';
import {getGiveCampaignsListTableWindowData} from '../CampaignsListTable';
import {amountFormatter} from '@givewp/campaigns/utils';
import TextareaControl from '../CampaignDetailsPage/Components/TextareaControl';
import {CampaignGoalInputAttributes, isValidGoalType} from '../../constants/goalInputAttributes';

const {currency, isRecurringEnabled} = getGiveCampaignsListTableWindowData();
const currencyFormatter = amountFormatter(currency);

/**
 * Get the next sharp hour
 *
 * @since 4.0.0
 */
const getNextSharpHour = (hoursToAdd: number) => {
    const date = new Date();
    date.setHours(date.getHours() + hoursToAdd, 0, 0, 0);

    return date;
};

/**
 * Format a given date to be used in datetime inputs
 *
 * @since 4.0.0
 */
const getDateString = (date: Date) => {
    const offsetInMilliseconds = date.getTimezoneOffset() * 60 * 1000;
    const dateWithOffset = new Date(date.getTime() - offsetInMilliseconds);

    return removeTimezoneFromDateISOString(dateWithOffset.toISOString());
};

/**
 * Remove timezone from date string
 *
 * @since 4.0.0
 */
const removeTimezoneFromDateISOString = (date: string) => {
    return date.slice(0, -5);
};

/**
 * @since 4.0.0
 */
const getGoalTypeIcon = (type: string) => {
    switch (type) {
        case 'amount':
            return <AmountIcon />;
        case 'donations':
            return <DonationsIcon />;
        case 'donors':
            return <DonorsIcon />;
        case 'amountFromSubscriptions':
            return <AmountFromSubscriptionsIcon />;
        case 'subscriptions':
            return <SubscriptionsIcon />;
        case 'donorsFromSubscriptions':
            return <DonorsFromSubscriptionsIcon />;
    }
};

/**
 * Goal Type Option component
 *
 * @since 4.0.0
 */
const GoalTypeOption = ({type, label, description, selected, register}: GoalTypeOptionType) => {
    const divRef = useRef(null);
    const labelRef = useRef(null);

    const handleDivClick = () => {
        labelRef.current.click();
    };

    return (
        <div
            className={`${styles.goalTypeOption}  ${selected ? styles.goalTypeOptionSelected : ''}`}
            ref={divRef}
            onClick={handleDivClick}
        >
            <div className={styles.goalTypeOptionIcon}>{getGoalTypeIcon(type)}</div>
            <div className={styles.goalTypeOptionText}>
                <label ref={labelRef}>
                    <input type="radio" value={type} {...register('goalType')} />
                    {label}
                </label>
                <span>{description}</span>
            </div>
        </div>
    );
};

/**
 * Campaign Form Modal component
 *
 * @since 4.0.0
 */
export default function CampaignFormModal({isOpen, handleClose, apiSettings, title, campaign}: CampaignModalProps) {
    const API = new CampaignsApi(apiSettings);
    const [step, setStep] = useState<number>(1);

    const methods = useForm<CampaignFormInputs>({
        defaultValues: {
            title: campaign?.title ?? '',
            shortDescription: campaign?.shortDescription ?? '',
            image: campaign?.image ?? '',
            goalType: campaign?.goalType ?? 'amount',
            goal: campaign?.goal ?? null,
            startDateTime: getDateString(
                campaign?.startDateTime?.date ? new Date(campaign?.startDateTime?.date) : getNextSharpHour(1)
            ),
            endDateTime: campaign?.endDateTime?.date ? getDateString(new Date(campaign.startDateTime.date)) : '',
        },
    });

    const {
        register,
        handleSubmit,
        formState: {errors, isDirty, isSubmitting},
        setValue,
        watch,
        trigger,
    } = methods;

    const image = watch('image');
    const selectedGoalType = watch('goalType');
    const goal = watch('goal');

    const getFormModalTitle = () => {
        switch (step) {
            case 1:
                return __('Tell us about your fundraising cause', 'give');
            case 2:
                return __('Set up your campaign goal', 'give');
        }

        return null;
    };

    const goalInputAttribute =
        selectedGoalType && isValidGoalType(selectedGoalType)
            ? new CampaignGoalInputAttributes(selectedGoalType, currency)
            : new CampaignGoalInputAttributes('amount', currency);

    const requiredAsterisk = <span className={`givewp-field-required ${styles.fieldRequired}`}>*</span>;

    const validateTitle = async () => {
        return await trigger('title');
    };

    const onSubmit: SubmitHandler<CampaignFormInputs> = async (inputs, event) => {
        event.preventDefault();

        if (step !== 2) {
            return;
        }

        try {
            inputs.startDateTime = getDateString(new Date(inputs.startDateTime));
            inputs.endDateTime = inputs.endDateTime && getDateString(new Date(inputs.endDateTime));

            const endpoint = campaign?.id ? `/campaign/${campaign.id}` : '';
            const response = await API.fetchWithArgs(endpoint, inputs, 'POST');

            handleClose(response);
        } catch (error) {
            console.error('Error submitting campaign campaign', error);
        }
    };

    return (
        <FormProvider {...methods}>
            <FormModal
                isOpen={isOpen}
                handleClose={handleClose}
                title={getFormModalTitle()}
                handleSubmit={handleSubmit(onSubmit)}
                errors={[]}
                className={styles.campaignForm}
            >
                {step === 1 && (
                    <>
                        <div className="givewp-campaigns__form-row">
                            <label htmlFor="title">
                                {__("What's the title of your campaign?", 'give')} {requiredAsterisk}
                            </label>
                            <span>{__("Give your campaign a title that tells donors what it's about.", 'give')}</span>
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
                            <span>{__('Let your donors know the story behind your campaign.', 'give')}</span>
                            <TextareaControl
                                name="shortDescription"
                                rows={4}
                                maxLength={120}
                                placeholder={__('Brief description for your campaign.', 'give')}
                            />
                        </div>
                        <div className="givewp-campaigns__form-row">
                            <label htmlFor="image">{__('Add a cover image for your campaign.', 'give')}</label>
                            <span>{__('Upload an image to represent and inspire your campaign.', 'give')}</span>
                            <Upload
                                id="givewp-campaigns-upload-cover-image"
                                label={__('Cover', 'give')}
                                actionLabel={__('Select to upload', 'give')}
                                value={image}
                                onChange={(coverImageUrl, coverImageAlt) => {
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
                )}
                {step === 2 && (
                    <>
                        <div className="givewp-campaigns__form-row">
                            <label htmlFor="goalType">
                                {__('How would you like to set your goal?', 'give')} {requiredAsterisk}
                            </label>
                            <span>{__('Set the goal your fundraising efforts will work toward.', 'give')}</span>
                            <div className={styles.goalType}>
                                <GoalTypeOption
                                    type={'amount'}
                                    label={__('Amount raised', 'give')}
                                    description={sprintf(
                                        __(
                                            'Your goal progress is measured by the total amount of funds raised eg. %s of %s raised.',
                                            'give'
                                        ),
                                        currencyFormatter.format(500),
                                        currencyFormatter.format(1000)
                                    )}
                                    selected={selectedGoalType === 'amount'}
                                    register={register}
                                />
                                <GoalTypeOption
                                    type={'donations'}
                                    label={__('Number of donations', 'give')}
                                    description={__(
                                        'Your goal progress is measured by the number of donations. eg. 1 of 5 donations.',
                                        'give'
                                    )}
                                    selected={selectedGoalType === 'donations'}
                                    register={register}
                                />
                                <GoalTypeOption
                                    type={'donors'}
                                    label={__('Number of donors', 'give')}
                                    description={__(
                                        'Your goal progress is measured by the number of donors. eg. 10 of 50 donors have given.',
                                        'give'
                                    )}
                                    selected={selectedGoalType === 'donors'}
                                    register={register}
                                />
                                {isRecurringEnabled && (
                                    <>
                                        <GoalTypeOption
                                            type={'amountFromSubscriptions'}
                                            label={__('Recurring amount raised', 'give')}
                                            description={__(
                                                'Only the first donation amount of a recurring donation is counted toward the goal.',
                                                'give'
                                            )}
                                            selected={selectedGoalType === 'amountFromSubscriptions'}
                                            register={register}
                                        />
                                        <GoalTypeOption
                                            type={'subscriptions'}
                                            label={__('Number of recurring donations', 'give')}
                                            description={__(
                                                'Only the first donation of a recurring donation is counted toward the goal.',
                                                'give'
                                            )}
                                            selected={selectedGoalType === 'subscriptions'}
                                            register={register}
                                        />
                                        <GoalTypeOption
                                            type={'donorsFromSubscriptions'}
                                            label={__('Number of recurring donors', 'give')}
                                            description={__(
                                                'Only the donors that subscribed to a recurring donation are counted toward the goal.',
                                                'give'
                                            )}
                                            selected={selectedGoalType === 'donorsFromSubscriptions'}
                                            register={register}
                                        />
                                    </>
                                )}
                            </div>
                            {errors.goalType && (
                                <div className={'givewp-campaigns__form-errors'}>
                                    <p>{errors.goalType.message}</p>
                                </div>
                            )}
                        </div>
                        {selectedGoalType && (
                            <div className="givewp-campaigns__form-row">
                                <label htmlFor="title">
                                    {goalInputAttribute.getLabel()} {requiredAsterisk}
                                </label>
                                <span>{goalInputAttribute.getDescription()}</span>
                                {selectedGoalType === 'amount' || selectedGoalType === 'amountFromSubscriptions' ? (
                                    <Currency
                                        name="goal"
                                        currency={currency}
                                        placeholder={goalInputAttribute.getPlaceholder()}
                                    />
                                ) : (
                                    <input
                                        type="number"
                                        {...register('goal', {valueAsNumber: true})}
                                        aria-invalid={errors.goal ? 'true' : 'false'}
                                        placeholder={goalInputAttribute.getPlaceholder()}
                                    />
                                )}
                                {errors.goal && (
                                    <div className={'givewp-campaigns__form-errors'}>
                                        <p>{errors.goal.message}</p>
                                    </div>
                                )}
                            </div>
                        )}
                        {/*<div className="givewp-campaigns__form-row givewp-campaigns__form-row--half">
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
                    </div>*/}
                        <div
                            className="givewp-campaigns__form-row givewp-campaigns__form-row--half"
                            style={{marginBottom: 0}}
                        >
                            <button
                                type="button"
                                onClick={() => setStep(1)}
                                className={`button button-secondary ${styles.button} ${styles.previousButton}`}
                            >
                                {__('Previous', 'give')}
                            </button>

                            <button
                                type="submit"
                                className={`button button-primary ${styles.button} ${
                                    !goal || isSubmitting ? 'disabled' : ''
                                }`}
                                aria-disabled={!goal || isSubmitting}
                                disabled={!goal || isSubmitting}
                            >
                                {__('Continue', 'give')}
                            </button>
                        </div>
                    </>
                )}
            </FormModal>
        </FormProvider>
    );
}
