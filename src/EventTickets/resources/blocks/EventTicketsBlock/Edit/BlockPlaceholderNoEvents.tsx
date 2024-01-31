import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';

export default function BlockPlaceholderNoEvents() {
    return (
        <div className={'givewp-event-tickets-block__placeholder--no-events'}>
            {createInterpolateElement(
                __('No events created yet. Go to the <a>events page</a> to create and manage your own event.', 'give'),
                {
                    a: <a href={window.eventTicketsBlockSettings.createEventUrl} target="_blank" />,
                }
            )}
        </div>
    );
}
