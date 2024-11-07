import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import styles from './Form.module.scss';
import FormModal from '../../FormModal';
import CampaignsApi from '../../api';
import {MergeCampaignFormInputs, MergeCampaignFormProps} from './types';
import {useState} from 'react';

/**
 * Campaign Form Modal component
 *
 * @unreleased
 */
export default function MergeCampaignForm({
    isOpen,
    handleClose,
    apiSettings,
    title,
    campaign,
    historyState,
}: MergeCampaignFormProps) {
    console.log('historyState:', historyState);
    const API = new CampaignsApi(apiSettings);
    const [step, setStep] = useState<number>(1);

    const methods = useForm<MergeCampaignFormInputs>({
        defaultValues: {
            title: campaign?.title ?? '',
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

    /*const image = watch('image');
    const selectedGoalType = watch('goalType');
    const goal = watch('goal');*/

    const getFormModalTitle = () => {
        switch (step) {
            case 1:
                return __('Merge campaigns - Step #1', 'give');
            case 2:
                return __('Merge campaigns - Step #2', 'give');
        }

        return null;
    };

    const requiredAsterisk = <span className={`givewp-field-required ${styles.fieldRequired}`}>*</span>;

    const validateTitle = async () => {
        return await trigger('title');
    };

    const onSubmit: SubmitHandler<MergeCampaignFormInputs> = async (inputs, event) => {
        event.preventDefault();

        if (step !== 2) {
            return;
        }

        try {
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
                        <div className="givewp-campaigns__form-row givewp-campaigns__form-row--half">
                            <button type="submit" onClick={() => setStep(1)} className={`button button-secondary`}>
                                {__('Previous', 'give')}
                            </button>

                            <button
                                type="submit"
                                className={`button button-primary ${isSubmitting ? 'disabled' : ''}`}
                                aria-disabled={false}
                                disabled={false}
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
