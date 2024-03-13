import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';

/**
 * @since 3.6.0
 */
export default function BlockPlaceholderNoEvents() {
    return (
        <div className={'givewp-event-tickets-block__placeholder--no-events'}>
            <p>
                {createInterpolateElement(
                    __(
                        'No events created yet. Go to the <a>events page</a> to create and manage your own event.',
                        'give'
                    ),
                    {
                        a: <a href={window.eventTicketsBlockSettings.listEventsUrl} target="_blank" />,
                    }
                )}
            </p>
        </div>
    );
}
