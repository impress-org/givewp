import type {Notification} from '../types';

export function getNotifications(state: []) {
    return state;
}

export function getNotificationsByType(state: [], type: 'snackbar' | 'notice') {
    return state.filter((notification: Notification) => notification.notificationType === type);
}
