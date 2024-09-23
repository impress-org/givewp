import {__} from '@wordpress/i18n';
import styles from '../style.module.scss';
import {useEntityRecord} from '@wordpress/core-data';
import {useEffect} from 'react';


type FormInputs = {
    title: string;
    longDescription: string;
    image: string;
    goalType: string;
    goal: number;
};

/**
 * @unreleased
 */
export default ({campaignId}) => {

    const {record, hasResolved, edit} = useEntityRecord('givewp', 'campaign', campaignId);


    useEffect(() => {
        edit({
            title: 'ajme'
        })
    }, []);

    return (
        <form>
            <div className={styles.sections}>
                <div className={styles.section}>
                    <div>
                        <h2>
                            {__('Campaign Details', 'give')}
                        </h2>
                        {__('This includes the campaign title, description, and the cover of your campaign.', 'give')}
                    </div>
                    <div>
                        R
                    </div>
                </div>
            </div>
        </form>
    );
}
