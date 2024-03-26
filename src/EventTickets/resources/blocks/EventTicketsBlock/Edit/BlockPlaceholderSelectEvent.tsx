import {__} from '@wordpress/i18n';
import {SelectControl} from '@wordpress/components';

/**
 * @since 3.6.0
 */
export default function BlockPlaceholderSelectEvent({attributes, setAttributes}) {
    const {events} = window.eventTicketsBlockSettings;
    const eventOptions =
        events.map((event) => {
            return {label: event.title, value: `${event.id}`};
        }) ?? [];

    return (
        <div className={'givewp-event-tickets-block__placeholder--select-event'}>
            <p>
                <strong>{__('No event selected yet', 'give')}</strong>
            </p>
            <SelectControl
                label={__('Select your preferred event for this donation form', 'give')}
                value={`${attributes.eventId}`}
                options={[{label: 'Select', value: ''}, ...eventOptions]}
                onChange={(value: string) => setAttributes({eventId: Number(value)})}
            />
        </div>
    );
}
