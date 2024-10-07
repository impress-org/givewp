import {select} from '@wordpress/data';

export default ({type}: {type: 'snackbar' | 'notice'}) => {
    //@ts-ignore
    const notifications = select('givewp/campaign-notifications').getNotificationsByType(type);

    if (!notifications.length) {
        return null;
    }

    return (
        <div>
            {notifications.map(notification => (
                <div>
                    {notification.content}
                </div>
            ))}
        </div>
    );
}
