import {__} from '@wordpress/i18n';
import {Notice} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';

import './styles.scss';

/**
 * @since 3.6.0
 */
export default function CreateEventNotice() {
    return (
        <div className={`givewp-event-tickets-block__create-event-notice`}>
            <Notice isDismissible={false} status="warning">
                <h4>{__('No event created yet', 'give')}</h4>
                <p>{__('Donors will not be able to see any event on this form', 'give')}</p>
                <p>{createInterpolateElement(
                    __('<a>Create an event</a>', 'give'),
                    {
                        a: <a href={window.eventTicketsBlockSettings.createEventUrl} target="_blank" />,
                    }
                )}</p>
            </Notice>
        </div>
    );
}
