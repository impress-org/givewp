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
                    'Your goal progress is measured by the total amount raised based on the goal amount. (e.g. $500 of $1,000 raised)',
                    'give'
                );
            case 'donation':
                return __('The total number of donations made for the campaign', 'give');
            case 'donors':
                return __('The total number of unique donors who have donated to the campaign', 'give');
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
                            <option value="donation">{__('Number of Donations', 'give')}</option>
                            <option value="donors">{__('Number of Donors', 'give')}</option>
                        </select>

                        <div className={styles.sectionFieldDescription}>{goalDescription(goalType)}</div>

                        {errors.goalType && <div className={styles.errorMsg}>{`${errors.goalType.message}`}</div>}
                    </div>

                    <div className={styles.sectionSubtitle}>{__('How much do you want to raise?', 'give')}</div>
                    <div className={styles.sectionFieldDescription}>
                        {__('Let us know the target amount you’re aiming for in your campaign.', 'give')}
                    </div>

                    {goalType === 'amount' ? (
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
