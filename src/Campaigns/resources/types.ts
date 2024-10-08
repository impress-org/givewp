export type Notification = {
    id: string;
    notificationType: 'notice' | 'snackbar';
    type: 'error' | 'warning' | 'info' | 'success';
    isDismissible?: boolean;
    duration: number,
    content: string;
}

declare module "@wordpress/data" {
    export function select(key: 'givewp/campaign-notifications'): {
        getNotifications(): Notification[],
        getNotificationsByType(type: 'snackbar' | 'notice'): Notification[]
    };

    export function dispatch(key: 'givewp/campaign-notifications'): {
        addSnackbarNotice(notification: Notification): void,
        addNotice(notification: Notification): void,
        dismissNotification(id: string): void
    };
}
