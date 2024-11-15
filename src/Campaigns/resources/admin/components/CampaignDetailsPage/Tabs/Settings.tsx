import {__} from '@wordpress/i18n';
import {useFormContext} from 'react-hook-form';
import {Currency, Editor, Upload} from '../../Inputs';
import {GiveCampaignDetails} from '../types';
import styles from '../CampaignDetailsPage.module.scss';
import {ToggleControl} from '@wordpress/components';
import campaignPage from './images/campaign-page.svg';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

/**
 * @unreleased
 */
export default () => {
    const {
        register,
        watch,
        setValue,
        formState: {errors},
    } = useFormContext();

    const [goalType, image, status, shortDescription, enableCampaignPage] = watch([
        'goalType',
        'image',
        'status',
        'shortDescription',
        'enableCampaignPage',
    ]);
    const isDisabled = status === 'archived';

    return (
        <div className={styles.sections}>
            <div className={styles.section}>
                <div className={styles.leftColumn}>
                    <div className={styles.sectionTitle}>{__('Campaign page', 'give')}</div>
                    <div className={styles.sectionDescription}>
                        {__(
                            'Set up a landing page for your campaign. The default campaign page has the campaign details, the campaign form, and donor wall.',
                            'give'
                        )}
                    </div>
                </div>
                <div className={styles.rightColumn}>
                    <div className={styles.sectionField}>
                        <img
                            style={{marginTop: '1rem'}}
                            src={campaignPage}
                            alt={__('Enable campaign page for your campaign.', 'give')}
                        />
                        <div className={styles.toggle}>
                            <ToggleControl
                                label={__('Enable campaign page for your campaign.', 'give')}
                                help={__('This will create a default campaign page for your campaign.', 'give')}
                                name="enableCampaignPage"
                                checked={enableCampaignPage}
                                onChange={(value) => {
                                    setValue('enableCampaignPage', value, {shouldDirty: true});
                                }}
                            />
                        </div>
                        {!enableCampaignPage && (
                            <div className={styles.warningNotice}>
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
                                        d="M10 .832a9.167 9.167 0 1 0 0 18.333A9.167 9.167 0 0 0 10 .832zm0 5a.833.833 0 1 0 0 1.667h.009a.833.833 0 0 0 0-1.667h-.008zm.834 4.167a.833.833 0 1 0-1.667 0v3.333a.833.833 0 1 0 1.667 0V9.999z"
                                        fill="#F29718"
                                    />
                                </svg>
                                <p>
                                    {__(
                                        'This will affect the campaign blocks associated with this campaign. Ensure that no campaign blocks are being used on any page.',
                                        'give'
                                    )}
                                </p>
                            </div>
                        )}
                        {errors.enableCampaignPage && (
                            <div className={styles.errorMsg}>{`${errors.enableCampaignPage.message}`}</div>
                        )}
                    </div>
                </div>
            </div>
            <div className={styles.section}>
                <div className={styles.leftColumn}>
                    <div className={styles.sectionTitle}>{__('Campaign Details', 'give')}</div>
                    <div className={styles.sectionDescription}>
                        {__('This includes the campaign title, description, and the cover of your campaign.', 'give')}
                    </div>
                </div>
                <div className={styles.rightColumn}>
                    <div className={styles.sectionField}>
                        <div className={styles.sectionSubtitle}>{__("What's the title of your campaign?", 'give')}</div>
                        <div className={styles.sectionFieldDescription}>
                            {__("Give your campaign a title that tells donors what it's about.", 'give')}
                        </div>

                        <input {...register('title')} disabled={isDisabled} />

                        {errors.title && <div className={styles.errorMsg}>{`${errors.title.message}`}</div>}
                    </div>

                    <div className={styles.sectionField}>
                        <div className={styles.sectionSubtitle}>{__("What's your campaign about?", 'give')}</div>
                        <div className={styles.sectionFieldDescription}>
                            {__('Let your donors know the story behind your campaign.', 'give')}
                        </div>

                        {isDisabled ? (
                            <textarea disabled={true} rows={10}>
                                {shortDescription.replace(/(<([^>]+)>)/gi, '')}
                            </textarea>
                        ) : (
                            <Editor name="shortDescription" />
                        )}

                        {errors.shortDescription && (
                            <div className={styles.errorMsg}>{`${errors.shortDescription.message}`}</div>
                        )}
                    </div>

                    <div className={styles.sectionField}>
                        <div className={styles.sectionSubtitle}>
                            {__('Add a cover image or video for your campaign.', 'give')}
                        </div>
                        <div className={styles.sectionFieldDescription}>
                            {__('Upload an image or video to represent and inspire your campaign.', 'give')}
                        </div>

                        <Upload
                            disabled={isDisabled}
                            id="givewp-campaigns-upload-cover-image"
                            label={__('Cover', 'give')}
                            actionLabel={__('Select to upload', 'give')}
                            value={image}
                            onChange={(coverImageUrl, coverImageAlt) => {
                                setValue('image', coverImageUrl, {shouldDirty: true});
                            }}
                            reset={() => setValue('image', '', {shouldDirty: true})}
                        />

                        {errors.title && <div className={styles.errorMsg}>{`${errors.title.message}`}</div>}
                    </div>
                </div>
            </div>

            <div className={styles.section}>
                <div className={styles.leftColumn}>
                    <div className={styles.sectionTitle}>{__('Campaign Goal', 'give')}</div>
                    <div className={styles.sectionDescription}>
                        {__('How would you like to set your goal?', 'give')}
                    </div>
                </div>
                <div className={styles.rightColumn}>
                    <div className={styles.sectionField}>
                        <div className={styles.sectionSubtitle}>
                            {__('Set the details of your campaign goal here.', 'give')}
                        </div>

                        <select {...register('goalType')} disabled={isDisabled}>
                            <option value="amount">{__('Amount raised', 'give')}</option>
                            <option value="donations">{__('Number of donations', 'give')}</option>
                            <option value="donors">{__('Number of donors', 'give')}</option>
                            {window.GiveCampaignDetails.isRecurringEnabled && (
                                <>
                                    <option value="amountFromSubscriptions">
                                        {__('Recurring amount raised', 'give')}
                                    </option>
                                    <option value="subscriptions">{__('Number of recurring donations', 'give')}</option>
                                    <option value="donorsFromSubscriptions">
                                        {__('Number of recurring donors', 'give')}
                                    </option>
                                </>
                            )}
                        </select>

                        <div className={styles.sectionFieldDescription}>{goalDescription(goalType)}</div>

                        {errors.goalType && <div className={styles.errorMsg}>{`${errors.goalType.message}`}</div>}
                    </div>

                    <div className={styles.sectionSubtitle}>{__('How much do you want to raise?', 'give')}</div>
                    <div className={styles.sectionFieldDescription}>
                        {__('Let us know the target amount you’re aiming for in your campaign.', 'give')}
                    </div>

                    {goalType === 'amount' || goalType === 'amountFromSubscriptions' ? (
                        <Currency name="goal" currency={window.GiveCampaignDetails.currency} disabled={isDisabled} />
                    ) : (
                        <input type="number" {...register('goal', {valueAsNumber: true})} disabled={isDisabled} />
                    )}

                    {errors.goal && <div className={styles.errorMsg}>{`${errors.goal.message}`}</div>}
                </div>
            </div>
        </div>
    );
};

const goalDescription = (type: string) => {
    switch (type) {
        case 'amount':
            return __(
                'Your goal progress is measured by the total amount of funds raised eg. $500 of $1,000 raised.',
                'give'
            );
        case 'donations':
            return __('Your goal progress is measured by the number of donations. eg. 1 of 5 donations.', 'give');
        case 'donors':
            return __(
                'Your goal progress is measured by the number of donors. eg. 10 of 50 donors have given.',
                'give'
            );
        case 'amountFromSubscriptions':
            return __('Only the first donation amount of a recurring donation is counted toward the goal.', 'give');
        case 'subscriptions':
            return __('Only the first donation of a recurring donation is counted toward the goal.', 'give');
        case 'donorsFromSubscriptions':
            return __('Only the donors that subscribed to a recurring donation are counted toward the goal.', 'give');
        default:
            return null;
    }
};
