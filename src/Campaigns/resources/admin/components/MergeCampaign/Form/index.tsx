import {FormProvider, SubmitHandler, useForm} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import styles from './Form.module.scss';
import FormModal from '../../FormModal';
import {MergeCampaignFormInputs, MergeCampaignFormProps} from './types';
import {useState} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import {getGiveCampaignsListTableWindowData} from '../../CampaignsListTable';

/**
 * Campaign Form Modal component
 *
 * @unreleased
 */
export default function MergeCampaignsForm({isOpen, handleClose, title, campaigns}: MergeCampaignFormProps) {
    if (!campaigns) {
        return <></>;
    }

    const [step, setStep] = useState<number>(1);

    const methods = useForm<MergeCampaignFormInputs>({
        defaultValues: {
            destinationCampaignId: '',
        },
    });

    const {
        register,
        handleSubmit,
        formState: {isDirty, isSubmitting},
        watch,
    } = methods;

    const destinationCampaignId = watch('destinationCampaignId');

    const getFormModalTitle = () => {
        if (4 === step) {
            return 'icon ' + title;
        }

        return title;
    };

    const requiredAsterisk = <span className={`givewp-field-required ${styles.fieldRequired}`}>*</span>;

    const onSubmit: SubmitHandler<MergeCampaignFormInputs> = async (inputs, event) => {
        event.preventDefault();

        if (step !== 2) {
            return;
        }

        const campaignsToMergeIds = campaigns.selected.filter((id) => id != inputs.destinationCampaignId);

        try {
            const response = await apiFetch({
                path: addQueryArgs('/give-api/v2/campaigns/' + destinationCampaignId + '/merge', {
                    campaignsToMergeIds: campaignsToMergeIds,
                }),
                method: 'PATCH',
            });

            console.log('Merge campaigns response: ', response);

            // Go to success page
            setStep(3);

            //Reset bulk actions selector
            const selects = document.querySelectorAll('#give-admin-campaigns-root select');
            selects.forEach((select) => {
                const selectElement = select as HTMLSelectElement;
                selectElement.selectedIndex = 0;
            });

            // Uncheck all checkboxes
            const checkboxes = document.querySelectorAll(".giveListTable input[type='checkbox']");
            checkboxes.forEach((checkbox) => {
                const input = checkbox as HTMLInputElement;
                input.checked = false;
            });
            // @ts-ignore
            document.querySelector('.giveListTable #giveListTableSelectAll').checked = false;

            //Remove campaignsToMergeIds from the list table.
            const adminFormsListViewItems = document.querySelectorAll('tr');
            if (adminFormsListViewItems.length > 0) {
                adminFormsListViewItems.forEach((itemElement) => {
                    const select = itemElement.querySelector('.giveListTableSelect');

                    if (!select) {
                        return;
                    }

                    const campaignId = select.getAttribute('data-id');
                    if (campaignsToMergeIds.includes(campaignId)) {
                        itemElement.remove();
                    }
                });
            }
            //handleClose(response);
        } catch (error) {
            // Go to error page
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
                            className={`button button-primary ${styles.button}`}
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
                            <select {...register('destinationCampaignId', {valueAsNumber: true})} defaultValue="">
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
                            className={`button button-primary ${styles.button} ${isSubmitting ? 'disabled' : ''}`}
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
                            <div className={styles.returnMessage}>
                                <svg
                                    width="40"
                                    height="40"
                                    viewBox="0 0 40 40"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        fillRule="evenodd"
                                        clipRule="evenodd"
                                        d="M20 1.664c-10.126 0-18.334 8.208-18.334 18.333 0 10.126 8.208 18.334 18.333 18.334 10.126 0 18.334-8.208 18.334-18.334 0-10.125-8.208-18.333-18.334-18.333zm8.678 14.512a1.667 1.667 0 0 0-2.357-2.357l-8.822 8.821-3.821-3.821a1.667 1.667 0 0 0-2.357 2.357l5 5c.65.65 1.706.65 2.357 0l10-10z"
                                        fill="#459948"
                                    />
                                </svg>

                                <label htmlFor="title">{__('Campaigns have been successfully merged', 'give')}</label>
                                <span>
                                    {__(
                                        'All donations, donors, and forms from selected campaigns now belong to your destination campaign.',
                                        'give'
                                    )}
                                </span>
                            </div>
                        </div>
                        <div
                            className="givewp-campaigns__form-row givewp-campaigns__form-row--half"
                            style={{marginBottom: 0}}
                        >
                            <button
                                type="submit"
                                onClick={() => handleClose()}
                                className={`button button-secondary ${styles.button} ${styles.previousButton}`}
                            >
                                {__('Back to campaign list', 'give')}
                            </button>

                            <button
                                type="submit"
                                onClick={() =>
                                    (window.location.href =
                                        getGiveCampaignsListTableWindowData().adminUrl +
                                        'edit.php?post_type=give_forms&page=give-campaigns&id=' +
                                        destinationCampaignId)
                                }
                                className={`button button-primary ${styles.button}`}
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
                            <div className={styles.returnMessage}>
                                <svg
                                    width="40"
                                    height="40"
                                    viewBox="0 0 40 40"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        fillRule="evenodd"
                                        clipRule="evenodd"
                                        d="M20 1.668c-10.125 0-18.333 8.208-18.333 18.333 0 10.125 8.208 18.334 18.333 18.334 10.125 0 18.333-8.209 18.333-18.334S30.125 1.668 20 1.668zm1.667 11.667a1.667 1.667 0 0 0-3.334 0V20a1.667 1.667 0 1 0 3.334 0v-6.666zM20 25a1.667 1.667 0 1 0 0 3.334h.017a1.667 1.667 0 0 0 0-3.334H20z"
                                        fill="#D92D0B"
                                    />
                                </svg>
                                <label htmlFor="title">{__("Campaigns couldn't be merged", 'give')}</label>
                                <span>
                                    {__(
                                        'An error occurred during the merging process. Please try again, or contact our support team if the issue persists.',
                                        'give'
                                    )}
                                </span>
                            </div>
                        </div>
                        <div
                            className="givewp-campaigns__form-row givewp-campaigns__form-row--half"
                            style={{marginBottom: 0}}
                        >
                            <button
                                type="submit"
                                onClick={() => handleClose()}
                                className={`button button-secondary ${styles.button} ${styles.previousButton}`}
                            >
                                {__('Back to campaign list', 'give')}
                            </button>

                            <button
                                type="submit"
                                onClick={() => setStep(2)}
                                className={`button button-primary ${styles.button}`}
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
