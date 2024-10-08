import type {Notification} from '@givewp/campaigns/types';

export function addSnackbarNotice(notification: Notification) {
    return {
        type: 'ADD_NOTIFICATION',
        notification: {
            ...notification,
            autoHide: notification?.autoHide ?? true,
            isDismissible: notification?.isDismissible ?? true,
            duration: notification?.duration ?? 5000,
            type: notification.type ?? 'info',
            notificationType: 'snackbar',
        },
    };
}

export function addNotice(notification: Notification) {
    return {
        type: 'ADD_NOTIFICATION',
        notification: {
            ...notification,
            autoHide: notification?.autoHide ?? false,
            isDismissible: notification?.isDismissible ?? true,
            duration: notification?.duration ?? 5000,
            type: notification.type ?? 'info',
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
