import {useBlockProps} from '@wordpress/block-editor';
import BlockInspectorControls from './BlockInspectorControls';
import BlockPlaceholderNoEvents from './BlockPlaceholderNoEvents';
import BlockPlaceholder from './BlockPlaceholder';
import BlockPlaceholderSelectEvent from './BlockPlaceholderSelectEvent';

import './styles.scss';

/**
 * @since TBD Render the block wrapper with useBlockProps() for block API version 3.
 * @since 3.6.0
 */
export default function Edit(props) {
    const blockProps = useBlockProps();
    const {events} = window.eventTicketsBlockSettings;
    const {
        attributes: {eventId},
    } = props;

    const eventIds = events.map((event) => event.id);

    return (
        <div {...blockProps}>
            {events.length === 0 ? (
                <BlockPlaceholderNoEvents />
            ) : !eventId || !eventIds.includes(eventId) ? (
                <BlockPlaceholderSelectEvent {...props} />
            ) : (
                <BlockPlaceholder {...props} />
            )}

            <BlockInspectorControls {...props} />
        </div>
    );
}
