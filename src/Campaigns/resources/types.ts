import {GiveCampaignDetails} from "@givewp/campaigns/admin/components/CampaignDetailsPage/types";

export type Notification = {
    id: string;
    content: string | JSX.Element | Function;
    notificationType?: 'notice' | 'snackbar';
    type?: 'error' | 'warning' | 'info' | 'success';
    isDismissible?: boolean;
    autoHide?: boolean;
    onDismiss?: () => void;
    duration?: number,
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
