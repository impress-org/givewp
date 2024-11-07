import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import styles from './CampaignFormModal.module.scss';
import FormModal from '../FormModal';
import CampaignsApi from '../api';
import {
    CampaignFormInputs,
    CampaignModalProps,
    GoalInputAttributes,
    GoalTypeOption as GoalTypeOptionType,
} from './types';
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

const {currency, isRecurringEnabled} = getGiveCampaignsListTableWindowData();

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
 * @unreleased
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
 * @unreleased
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
 * @unreleased
 */
export default function CampaignFormModal({isOpen, handleClose, apiSettings, title, campaign}: CampaignModalProps) {
    const API = new CampaignsApi(apiSettings);
    const [step, setStep] = useState<number>(1);

    const methods = useForm<CampaignFormInputs>({
        defaultValues: {
            title: campaign?.title ?? '',
            shortDescription: campaign?.shortDescription ?? '',
            image: campaign?.image ?? '',
            goalType: campaign?.goalType ?? '',
            goal: campaign?.goal ?? null,
            startDateTime: getDateString(
                campaign?.startDateTime?.date ? new Date(campaign?.startDateTime?.date) : getNextSharpHour(1)
            ),
            endDateTime: getDateString(
                campaign?.endDateTime?.date ? new Date(campaign?.endDateTime?.date) : getNextSharpHour(2)
            ),
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

    const goalInputAttributes: {[selectedGoalType: string]: GoalInputAttributes} = {
        amount: {
            label: __('How much do you want to raise?', 'give'),
            description: __('Set the target amount your campaign should raise.', 'give'),
            placeholder: __('eg. $2,000', 'give'),
        },
        donations: {
            label: __('How many donations do you need?', 'give'),
            description: __('Set the target number of donations your campaign should bring in.', 'give'),
            placeholder: __('eg. 100 donations', 'give'),
        },
        donors: {
            label: __('How many donors do you need?', 'give'),
            description: __('Set the target number of donors your campaign should bring in.', 'give'),
            placeholder: __('eg. 100 donors', 'give'),
        },
        amountFromSubscriptions: {
            label: __('How much do you want to raise?', 'give'),
            description: __(
                'Set the target recurring amount your campaign should raise. One-time donations do not count.',
                'give'
            ),
            placeholder: __('eg. $2,000', 'give'),
        },
        subscriptions: {
            label: __('How many recurring donations do you need?', 'give'),
            description: __(
                'Set the target number of recurring donations your campaign should bring in. One-time donations do not count.',
                'give'
            ),
            placeholder: __('eg. 100 subscriptions', 'give'),
        },
        donorsFromSubscriptions: {
            label: __('How many recurring donors do you need?', 'give'),
            description: __(
                'Set the target number of recurring donors your campaign should bring in. One-time donations do not count.',
                'give'
            ),
            placeholder: __('eg. 100 subscribers', 'give'),
        },
    };

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
            inputs.endDateTime = getDateString(new Date(inputs.endDateTime));

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
                            <div className={styles.description}>
                                {__("Give your campaign a title that tells donors what it's about.", 'give')}
                            </div>
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
                            <div className={styles.description}>
                                {__('Let your donors know the story behind your campaign.', 'give')}
                            </div>
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
                            <label htmlFor="image">{__('Add a cover image or video for your campaign.', 'give')}</label>
                            <div className={styles.description}>
                                {__('Upload an image or video to represent and inspire your campaign.', 'give')}
                            </div>
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
                            <span className={styles.description}>
                                {__('Set the goal your fundraising efforts will work toward.', 'give')}
                            </span>
                            <div className={styles.goalType}>
                                <GoalTypeOption
                                    type={'amount'}
                                    label={__('Amount raised', 'give')}
                                    description={__(
                                        'Your goal progress is measured by the total amount of funds raised eg. $500 of $1,000 raised.',
                                        'give'
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
                                    {goalInputAttributes[selectedGoalType].label} {requiredAsterisk}
                                </label>
                                <span className={styles.description}>
                                    {goalInputAttributes[selectedGoalType].description}
                                </span>
                                {selectedGoalType === 'amount' || selectedGoalType === 'amountFromSubscriptions' ? (
                                    <Currency
                                        name="goal"
                                        currency={currency}
                                        placeholder={goalInputAttributes[selectedGoalType].placeholder}
                                    />
                                ) : (
                                    <input
                                        type="number"
                                        {...register('goal', {valueAsNumber: true})}
                                        aria-invalid={errors.goal ? 'true' : 'false'}
                                        placeholder={goalInputAttributes[selectedGoalType].placeholder}
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
                        <div className="givewp-campaigns__form-row givewp-campaigns__form-row--half">
                            <button type="submit" onClick={() => setStep(1)} className={`button button-secondary`}>
                                {__('Previous', 'give')}
                            </button>

                            <button
                                type="submit"
                                className={`button button-primary ${!goal || isSubmitting ? 'disabled' : ''}`}
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
