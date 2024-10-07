import {createReduxStore} from '@wordpress/data';

export type Notification = {
    id: string;
    notificationType: 'notice' | 'snackbar';
    type: 'error' | 'warning' | 'info' | 'success';
    isDismissible?: boolean;
    duration: number,
    content: string;
}

export const storeName = 'givewp/campaign-notifications';

export const store = createReduxStore(storeName, {
    reducer(state = [], action) {
        switch (action.type) {
            case 'ADD_NOTIFICATION':
                state.push(action.notification);
                return state;

            case 'DISMISS_NOTIFICATION':
                return state.filter((notification: Notification) => notification.id !== action.id);
        }

        return state;
    },

    actions: {
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
    },
    selectors: {
        getNotifications(state: []) {
            return state;
        },

        getNotificationsByType(state: [], type: 'snackbar' | 'notice') {
            return state.filter((notification: Notification) => notification.notificationType === type);
        },
    },

});


