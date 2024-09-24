import {__} from '@wordpress/i18n';
import {useFormContext} from 'react-hook-form';
import CurrencyInput from '../Components/CurrencyInput';
import {GiveCampaignDetails} from '../types';

import styles from '../style.module.scss';

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
        formState: {errors}
    } = useFormContext();

    const goalType = watch('goalType');
    const goal = watch('goal');

    console.log(goal)

    return (
        <div className={styles.sections}>
            <div className={styles.section}>
                <div>
                    <div className={styles.sectionTitle}>
                        {__('Campaign Details', 'give')}
                    </div>
                    <div className={styles.sectionDescription}>
                        {__('This includes the campaign title, description, and the cover of your campaign.', 'give')}
                    </div>

                </div>
                <div>
                    <div className={styles.sectionSubtitle}>
                        {__('What\'s the title of your campaign?', 'give')}
                    </div>
                    <div className={styles.sectionFieldDescription}>
                        {__('Give your campaign a title that tells donors what it’s about.', 'give')}
                    </div>

                    <input {...register('title')} />

                    {errors.title && (
                        <div className={styles.errorMsg}>
                            {`${errors.title.message}`}
                        </div>
                    )}
                </div>
            </div>

            <div className={styles.section}>
                <div>
                    <div className={styles.sectionTitle}>
                        {__('Campaign Goal', 'give')}
                    </div>
                    <div className={styles.sectionDescription}>
                        {__('How would you like to set your goal?', 'give')}
                    </div>
                </div>
                <div>
                    <div className={styles.sectionSubtitle}>
                        {__('Set the details of your campaign goal here.', 'give')}
                    </div>

                    <select {...register('goalType')}>
                        <option value="amount">
                            {__('Amount raised', 'give')}
                        </option>
                        <option value="donation">
                            {__('Number of Donations', 'give')}
                        </option>
                        <option value="donors">
                            {__('Number of Donors', 'give')}
                        </option>
                    </select>

                    {errors.goalType && (
                        <div className={styles.errorMsg}>
                            {`${errors.goalType.message}`}
                        </div>
                    )}

                    <div className={styles.sectionSubtitle}>
                        {__('How much do you want to raise?', 'give')}
                    </div>
                    <div className={styles.sectionFieldDescription}>
                        {__('Let us know the target amount you’re aiming for in your campaign.', 'give')}
                    </div>

                    {goalType === 'amount' ? (
                        <CurrencyInput name="goal" currency={window.GiveCampaignDetails.currency} />
                    ) : (
                        <input type="number" {...register('goal', {valueAsNumber: true})} />
                    )}

                    {errors.goal && (
                        <div className={styles.errorMsg}>
                            {`${errors.goal.message}`}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
