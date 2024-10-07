import {Notification} from './index';

const actions = {
    addSnackbarNotice(notification: Notification) {
        return {
            type: 'ADD_NOTIFICATION',
            notification: {
                ...notification,
                duration: notification?.duration ?? 3000,
                notificationType: 'snackbar',
            },
        };
    },

    addNotice(notification: Notification) {
        return {
            type: 'ADD_NOTIFICATION',
            notification: {
                ...notification,
                duration: notification?.duration ?? 3000,
                notificationType: 'notice',
            },
        };
    },

    dismissNotification(id: string) {
        return {
            type: 'DISMISS_NOTIFICATION',
            id,
        };
    },
}

export default actions;
