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
                    <input {...register('title')} />

                    {errors.title && (
                        // @ts-ignore
                        <div className="error-message">{errors.title.message}</div>
                    )}
                </div>
            </div>
        </div>
    );
}
