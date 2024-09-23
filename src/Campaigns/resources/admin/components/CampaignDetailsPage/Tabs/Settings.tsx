import {__} from '@wordpress/i18n';
import {useFormContext} from 'react-hook-form';

import styles from '../style.module.scss';

/**
 * @unreleased
 */
export default () => {

    const {register, formState: {errors}} = useFormContext();

    return (
        <div className={styles.sections}>
            <div className={styles.section}>
                <div>
                    <h2>
                        {__('Campaign Details', 'give')}
                    </h2>
                    {__('This includes the campaign title, description, and the cover of your campaign.', 'give')}
                </div>
                <div>
                    <h3>
                        {__("What's the title of your campaign?", 'give')}
                    </h3>
                    <div>
                        {__('Give your campaign a title that tells donors what it’s about.', 'give')}
                    </div>

                    <input {...register('title')} />

                    {errors.title && (
                        // @ts-ignore
                        <div className="error-message">{errors.title.message}</div>
                    )}
                </div>
            </div>

            <div className={styles.section}>
                <div>
                    <h2>
                        {__('Campaign Goal', 'give')}
                    </h2>
                    {__('How would you like to set your goal?', 'give')}
                </div>
                <div>
                    <h3>
                        {__('Set the details of your campaign goal here.', 'give')}
                    </h3>

                    <select {...register('goalType')}>
                        <option value="amount">
                            {__('Amount raised', 'give')}
                        </option>
                        <option value="donations">
                            {__('Donations', 'give')}
                        </option>
                    </select>

                    {errors.goalType && (
                        // @ts-ignore
                        <div className="error-message">{errors.goalType.message}</div>
                    )}

                    <h3>
                        {__('How much do you want to raise?', 'give')}
                    </h3>
                    <div>
                        {__('Let us know the target amount you’re aiming for in your campaign.', 'give')}
                    </div>

                    <input type="number" {...register('goal')} />

                    {errors.goal && (
                        // @ts-ignore
                        <div className="error-message">{errors.goal.message}</div>
                    )}
                </div>
            </div>
        </div>
    );
}
