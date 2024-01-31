import BlockInspectorControls from './BlockInspectorControls';
import BlockPlaceholderNoEvents from './BlockPlaceholderNoEvents';
import BlockPlaceholder from './BlockPlaceholder';
import BlockPlaceholderSelectEvent from './BlockPlaceholderSelectEvent';

export default function Edit(props) {
    const {events} = window.eventTicketsBlockSettings;
    const {
        attributes: {eventId},
    } = props;

    return (
        <>
            {events.length === 0 ? (
                <BlockPlaceholderNoEvents />
            ) : !eventId ? (
                <BlockPlaceholderSelectEvent {...props} />
            ) : (
                <BlockPlaceholder {...props} />
            )}

            <BlockInspectorControls {...props} />
        </>
    );
}
