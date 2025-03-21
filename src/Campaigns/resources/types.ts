/**
 * @unreleased
 */
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

/**
 * @unreleased
 */
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

/**
 * @unreleased
 */
export type GiveCampaignOptions = {
    isAdmin: boolean;
    adminUrl: string;
    campaignsAdminUrl: string;s
    currency: string;
    isRecurringEnabled: boolean;
    defaultForm: string;
    admin: {
        showCampaignInteractionNotice: boolean
        showExistingUserIntroNotice: boolean
    }
}

/**
 * @unreleased
 */
export type GoalType =
    'amount'
    | 'donations'
    | 'donors'
    | 'amountFromSubscriptions'
    | 'subscriptions'
    | 'donorsFromSubscriptions';
