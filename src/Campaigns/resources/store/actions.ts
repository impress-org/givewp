import type {Notification} from '@givewp/campaigns/types';

export function addSnackbarNotice(notification: Notification) {
    return {
        type: 'ADD_NOTIFICATION',
        notification: {
            ...notification,
            duration: notification?.duration ?? 3000,
            notificationType: 'snackbar',
        },
    };
}

export function addNotice(notification: Notification) {
    return {
        type: 'ADD_NOTIFICATION',
        notification: {
            ...notification,
            duration: notification?.duration ?? 3000,
            notificationType: 'notice',
        },
    };
}

export function dismissNotification(id: string) {
    return {
        type: 'DISMISS_NOTIFICATION',
        id,
    };
}
