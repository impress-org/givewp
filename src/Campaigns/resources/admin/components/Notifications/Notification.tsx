import {useEffect} from '@wordpress/element';
import {dispatch} from '@wordpress/data';
import cx from 'classnames';
import type {Notification} from '@givewp/campaigns/types';
import {CloseIcon} from '../Icons';

import styles from './Notices.module.scss';

const Snackbar = ({notification, onDismiss}: {notification: Notification, onDismiss: () => void}) => {
    return (

        <div
            className={cx(styles.snackbar, styles[`type-${notification.type}-snackbar`])}
        >
            <div>
                {notification.content}
            </div>
            {notification.isDismissible && (
                <a href="#" onClick={onDismiss}>
                    <CloseIcon />
                </a>
            )}

        </div>
    );
};

const Notice = ({notification, onDismiss}: {notification: Notification, onDismiss: () => void}) => {
    return (

        <div
            className={cx(styles.notice, styles[`type-${notification.type}`])}
        >
            <div>
                {notification.content}
            </div>
            {notification.isDismissible && (
                <a href="#" onClick={onDismiss}>
                    <CloseIcon />
                </a>
            )}

        </div>
    );
};

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


    return notification.notificationType === 'snackbar'
        ? (
            <Snackbar
                notification={notification}
                onDismiss={onDismiss}
            />
        ) : (
            <Notice
                notification={notification}
                onDismiss={onDismiss}
            />
        );

}
