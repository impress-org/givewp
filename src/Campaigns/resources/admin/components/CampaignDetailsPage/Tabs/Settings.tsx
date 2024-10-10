import {__} from '@wordpress/i18n';
import {useFormContext} from 'react-hook-form';
import {Currency, Editor, Upload} from '../../Inputs';
import {GiveCampaignDetails} from '../types';
import styles from '../CampaignDetailsPage.module.scss';

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
                return __(
                    'Only the donors that subscribed to a recurring donation are counted toward the goal.',
                    'give'
                );
            default:
                return null;
        }
    };

    const goalType = watch('goalType');
    const image = watch('image');

    return (
        <div className={styles.sections}>
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

                        <input {...register('title')} />

                        {errors.title && <div className={styles.errorMsg}>{`${errors.title.message}`}</div>}
                    </div>

                    <div className={styles.sectionField}>
                        <div className={styles.sectionSubtitle}>{__("What's your campaign about?", 'give')}</div>
                        <div className={styles.sectionFieldDescription}>
                            {__('Let your donors know the story behind your campaign.', 'give')}
                        </div>

                        <Editor name="shortDescription" />

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

                        <select {...register('goalType')}>
                            <option value="amount">{__('Amount raised', 'give')}</option>
                            <option value="donations">{__('Number of donations', 'give')}</option>
                            <option value="donors">{__('Number of donors', 'give')}</option>
                            {window.GiveCampaignDetails.isRecurringEnabled && (
                                <option value="amountFromSubscriptions">{__('Recurring amount raised', 'give')}</option>
                            )}
                            {window.GiveCampaignDetails.isRecurringEnabled && (
                                <option value="subscriptions">{__('Number of recurring donations', 'give')}</option>
                            )}
                            {window.GiveCampaignDetails.isRecurringEnabled && (
                                <option value="donorsFromSubscriptions">
                                    {__('Number of recurring donors', 'give')}
                                </option>
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
                        <Currency name="goal" currency={window.GiveCampaignDetails.currency} />
                    ) : (
                        <input type="number" {...register('goal', {valueAsNumber: true})} />
                    )}

                    {errors.goal && <div className={styles.errorMsg}>{`${errors.goal.message}`}</div>}
                </div>
            </div>
        </div>
    );
};
