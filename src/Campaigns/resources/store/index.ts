import {createReduxStore} from '@wordpress/data';
import actions from './actions';
import selectors from './selectors';

export type Notification = {
    id: string;
    notificationType: 'notice' | 'snackbar';
    type: 'error' | 'warning' | 'info' | 'success';
    isDismissible?: boolean;
    duration: number,
    content: string;
}

export const store = createReduxStore('givewp/campaign-notifications', {
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
    actions,
    selectors,
});


