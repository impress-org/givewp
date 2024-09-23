import {__} from '@wordpress/i18n';
import {useEntityRecord} from '@wordpress/core-data';

import styles from '../style.module.scss';
import Spinner from '@givewp/components/Spinner';

/**
 * @unreleased
 */
export default ({campaignId}) => {

    const {record, hasResolved} = useEntityRecord('givewp', 'campaign', campaignId);


    if (!hasResolved) {
        return <Spinner />
    }

    return (
        <div>
            <div>
                Overview
            </div>
            {JSON.stringify(record, null, 2)}
        </div>
    );
}
