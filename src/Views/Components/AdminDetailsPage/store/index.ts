import {createReduxStore, register, select} from '@wordpress/data';
import * as actions from './actions';
import * as selectors from './selectors';

export type Notification = {
    id: string;
    notificationType: 'notice' | 'snackbar';
    type: 'error' | 'warning' | 'info' | 'success';
    isDismissible?: boolean;
    duration: number,
    content: string;
}

const STORE_NAME = 'givewp/admin-details-page-notifications';

export const store = createReduxStore(STORE_NAME, {
    reducer(state = [], action) {
        switch (action.type) {
            case 'ADD_NOTIFICATION':
                const notificationExist = state.filter((notification: { id: string }) => notification.id === action.notification.id);
                if (!notificationExist.length) {
                    return [...state, action.notification];
                }
                return state;

            case 'DISMISS_NOTIFICATION':
                return state.filter((notification: Notification) => notification.id !== action.id);
        }

        return state;
    },
    actions,
    selectors,
});

/**
 * Two bundles can load this store on one page (the campaign details page and its "Forms" tab), and
 * registering a store name twice is an error in `@wordpress/data`.
 *
 * @since TBD
 */
if (!select(STORE_NAME)) {
    register(store);
}
