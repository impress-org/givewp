import BlockInspectorControls from './BlockInspectorControls';
import BlockPlaceholderNoEvents from './BlockPlaceholderNoEvents';
import BlockPlaceholder from './BlockPlaceholder';
import BlockPlaceholderSelectEvent from './BlockPlaceholderSelectEvent';

import './styles.scss';

/**
 * @since 3.6.0
 */
export default function Edit(props) {
    const {events} = window.eventTicketsBlockSettings;
    const {
        attributes: {eventId},
    } = props;

    const eventIds = events.map((event) => event.id);

    return (
        <>
            {events.length === 0 ? (
                <BlockPlaceholderNoEvents />
            ) : !eventId || !eventIds.includes(eventId) ? (
                <BlockPlaceholderSelectEvent {...props} />
            ) : (
                <BlockPlaceholder {...props} />
            )}

            <BlockInspectorControls {...props} />
        </>
    );
}
