/**
 * @since 4.0.0
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
 * @since 4.0.0
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
 * @since 4.0.0
 */
export type GiveCampaignOptions = {
    isAdmin: boolean;
    adminUrl: string;
    apiRoot: string;
    apiNonce: string;
    campaignsAdminUrl: string;s
    currency: string;
    isRecurringEnabled: boolean;
    defaultForm: string;
    admin: {
        showCampaignInteractionNotice: boolean
        showFormGoalNotice: boolean
        showExistingUserIntroNotice: boolean
        showCampaignListTableNotice: boolean
        showCampaignFormNotice: boolean
        showCampaignSettingsNotice: boolean
    }
}

/**
 * @since 4.0.0
 */
export type GoalType =
    'amount'
    | 'donations'
    | 'donors'
    | 'amountFromSubscriptions'
    | 'subscriptions'
    | 'donorsFromSubscriptions';
