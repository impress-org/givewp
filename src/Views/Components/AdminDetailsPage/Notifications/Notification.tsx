import {__} from '@wordpress/i18n';
import {useEffect} from '@wordpress/element';
import {dispatch} from '@wordpress/data';
import cx from 'classnames';
import type {Notification} from '../types';
import {CloseIcon} from '../Icons';

import styles from './Notices.module.scss';

const Snackbar = ({notification, onDismiss}: { notification: Notification, onDismiss: () => void }) => {
    return (

        <div
            className={cx(styles.snackbar, styles[`type-${notification.type}-snackbar`])}
            role={notification.type === 'error' ? 'alert' : 'status'}
        >
            <div>
                {typeof notification.content === 'function' ? notification.content(onDismiss, notification) : notification.content}
            </div>
            {notification.isDismissible && (
                <a href="#" onClick={onDismiss} aria-label={__('Dismiss', 'give')}>
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
            role={notification.type === 'error' ? 'alert' : 'status'}
        >
            <div className={styles.notificationContent}>
                {typeof notification.content === 'function' ? notification.content(onDismiss, notification) : notification.content}
            </div>
            {notification.isDismissible && (
                <a href="#" onClick={onDismiss} aria-label={__('Dismiss', 'give')}>
                    <CloseIcon />
                </a>
            )}

        </div>
    );
};

/**
 * @since TBD Announce notifications to assistive technology and label the dismiss control.
 */
export default ({notification}: { notification: Notification }) => {

    useEffect(() => {
        if (notification.autoHide) {
            setTimeout(() => {
                dispatch('givewp/admin-details-page-notifications').dismissNotification(notification.id);
            }, notification.duration);
        }
    }, []);

    const onDismiss = () => {
        dispatch('givewp/admin-details-page-notifications').dismissNotification(notification.id);

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
            return null;
    }
}
