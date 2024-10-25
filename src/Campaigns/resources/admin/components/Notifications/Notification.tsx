import {useEffect} from '@wordpress/element';
import {dispatch} from '@wordpress/data';
import cx from 'classnames';
import type {Notification} from '@givewp/campaigns/types';
import {CloseIcon} from '../Icons';

import styles from './Notices.module.scss';

const Snackbar = ({notification, onDismiss}: { notification: Notification, onDismiss: () => void }) => {
    return (

        <div
            className={cx(styles.snackbar, styles[`type-${notification.type}-snackbar`])}
        >
            <div>
                {typeof notification.content === 'function' ? notification.content() : notification.content}
            </div>
            {notification.isDismissible && (
                <a href="#" onClick={onDismiss}>
                    <CloseIcon />
                </a>
            )}

        </div>
    );
};

const Notice = ({notification, onDismiss}: { notification: Notification, onDismiss: () => void }) => {
    return (

        <div
            className={cx(styles.notice, styles[`type-${notification.type}`])}
        >
            <div className={styles.notificationContent}>
                {typeof notification.content === 'function' ? notification.content() : notification.content}
            </div>
            {notification.isDismissible && (
                <a href="#" onClick={onDismiss}>
                    <CloseIcon />
                </a>
            )}

        </div>
    );
};

const Custom = ({notification, onDismiss}: { notification: Notification, onDismiss: () => void }) => (
    typeof notification.content === 'function' ? notification.content(onDismiss, notification) : notification.content
)

export default ({notification}) => {

    useEffect(() => {
        if (notification.autoHide) {
            setTimeout(() => {
                dispatch('givewp/campaign-notifications').dismissNotification(notification.id);
            }, notification.duration);
        }
    }, []);

    const onDismiss = () => {
        dispatch('givewp/campaign-notifications').dismissNotification(notification.id);

        if (typeof notification.onDismiss === 'function') {
            notification.onDismiss();
        }
    };


    switch (notification.notificationType) {
        case 'snackbar':
            return <Snackbar notification={notification} onDismiss={onDismiss} />
        case 'notice':
            return <Notice notification={notification} onDismiss={onDismiss} />
        default:
            return <Custom notification={notification} onDismiss={onDismiss} />
    }
}
