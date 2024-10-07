import {Notification} from './index';

const selectors = {
    getNotifications(state: []) {
        return state;
    },

    getNotificationsByType(state: [], type: 'snackbar' | 'notice') {
        return state.filter((notification: Notification) => notification.notificationType === type);
    },
}

export default selectors;
