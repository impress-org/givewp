import {useSelect} from '@wordpress/data';
import Notification from './Notification';
import styles from './Notices.module.scss';

export default ({type}: {type: 'snackbar' | 'notice'}) => {
    //@ts-ignore
    const notifications = useSelect(select => select('givewp/campaign-notifications').getNotificationsByType(type));

    if (!notifications.length) {
        return null;
    }

    return (
        <div className={styles[`${type}Container`]}>
            {notifications.map(notification => <Notification notification={notification} />)}
        </div>
    );
}
