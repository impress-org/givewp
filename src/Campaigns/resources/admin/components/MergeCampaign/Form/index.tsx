import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import styles from './Form.module.scss';
import FormModal from '../../FormModal';
import {MergeCampaignFormInputs, MergeCampaignFormProps} from './types';
import {useState} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

/**
 * Campaign Form Modal component
 *
 * @unreleased
 */
export default function MergeCampaignsForm({isOpen, handleClose, title, campaigns}: MergeCampaignFormProps) {
    console.log('campaigns:', campaigns);

    if (!campaigns) {
        return <></>;
    }

    const [step, setStep] = useState<number>(1);

    const methods = useForm<MergeCampaignFormInputs>({
        defaultValues: {
            destinationCampaign: '',
        },
    });

    const {
        register,
        handleSubmit,
        formState: {errors, isDirty, isSubmitting},
    } = methods;

    const getFormModalTitle = () => {
        if (4 === step) {
            return 'icon ' + title;
        }

        return title;
    };

    const requiredAsterisk = <span className={`givewp-field-required ${styles.fieldRequired}`}>*</span>;

    const onSubmit: SubmitHandler<MergeCampaignFormInputs> = async (inputs, event) => {
        event.preventDefault();

        if (step !== 2 && step !== 4) {
            return;
        }

        const destinationCampaign = inputs.destinationCampaign;
        const campaignsToMergeIds = campaigns.selected.filter((id) => id != destinationCampaign);

        console.log('destinationCampaign: ', destinationCampaign);
        console.log('campaignsToMergeIds: ', campaignsToMergeIds);

        try {
            const response = await apiFetch({
                path: addQueryArgs('/give-api/v2/campaigns/' + destinationCampaign + '/merge', {
                    campaignsToMergeIds: campaignsToMergeIds,
                }),
                method: 'PATCH',
            });

            console.log('Merge campaigns response: ', response);

            setStep(3);
            //handleClose(response);
        } catch (error) {
            setStep(4);
            console.error('Error merging campaigns: ', error);
        }
    };

    const extractTextFromLink = (link) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(link, 'text/html');
        return doc.querySelector('a')?.textContent || link;
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
                            <p className={styles.intro}>
                                {__(
                                    'All selected campaigns will be merged into the destination campaign. This means that forms, donors, donations, and all related data will be added to the destination campaign, and the merged campaigns will cease to exist.',
                                    'give'
                                )}
                            </p>
                        </div>
                        <button
                            type="submit"
                            onClick={() => setStep(2)}
                            className={`button button-primary`}
                            aria-disabled={false}
                            disabled={false}
                        >
                            {__('Proceed', 'give')}
                        </button>
                    </>
                )}
                {step === 2 && (
                    <>
                        <div className="givewp-campaigns__form-row">
                            <label htmlFor="title">
                                {__('Select your destination campaign', 'give')} {requiredAsterisk}
                            </label>
                            <span className={styles.description}>
                                {__('All selected campaigns will be merged into this campaign.', 'give')}
                            </span>
                            <select {...register('destinationCampaign', {valueAsNumber: true})} defaultValue="">
                                <option value="" disabled hidden>
                                    {__('Choose from selected campaigns', 'give')}
                                </option>
                                {campaigns.selected.map((id, index) => (
                                    <option key={id} value={id}>
                                        {extractTextFromLink(campaigns.names[index])}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <button
                            type="submit"
                            className={`button button-primary ${isSubmitting ? 'disabled' : ''}`}
                            aria-disabled={!isDirty}
                            disabled={!isDirty}
                        >
                            {isSubmitting ? __('Merging in progress', 'give') : __('Merge', 'give')}
                        </button>
                        {isDirty && (
                            <div className={styles.notice}>
                                <svg
                                    width="20"
                                    height="20"
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        fillRule="evenodd"
                                        clipRule="evenodd"
                                        d="M10 .836a9.167 9.167 0 1 0 0 18.333A9.167 9.167 0 0 0 10 .836zm0 5a.833.833 0 1 0 0 1.667h.009a.833.833 0 0 0 0-1.667h-.008zm.834 4.167a.833.833 0 0 0-1.667 0v3.333a.833.833 0 1 0 1.667 0v-3.333z"
                                        fill="#0C7FF2"
                                    />
                                </svg>

                                <p>{__('Once completed, this action is irreversible.', 'give')}</p>
                            </div>
                        )}
                    </>
                )}
                {step === 3 && (
                    <>
                        <div className="givewp-campaigns__form-row">
                            <p>Confirmation Page</p>
                        </div>
                        <div className="givewp-campaigns__form-row givewp-campaigns__form-row--half">
                            <button type="submit" onClick={() => handleClose()} className={`button button-secondary`}>
                                {__('Back to campaign list', 'give')}
                            </button>

                            <button
                                type="submit"
                                className={`button button-primary ${isSubmitting ? 'disabled' : ''}`}
                                aria-disabled={false}
                                disabled={false}
                            >
                                {__('View destination campaign', 'give')}
                            </button>
                        </div>
                    </>
                )}
                {step === 4 && (
                    <>
                        <div className="givewp-campaigns__form-row">
                            <p>Error Page</p>
                        </div>
                        <div className="givewp-campaigns__form-row givewp-campaigns__form-row--half">
                            <button type="submit" onClick={() => handleClose()} className={`button button-secondary`}>
                                {__('Back to campaign list', 'give')}
                            </button>

                            <button
                                type="submit"
                                className={`button button-primary ${isSubmitting ? 'disabled' : ''}`}
                                aria-disabled={false}
                                disabled={false}
                            >
                                {__('Try again', 'give')}
                            </button>
                        </div>
                    </>
                )}
            </FormModal>
        </FormProvider>
    );
}
