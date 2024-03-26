import {InspectorControls} from '@wordpress/block-editor';
import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import CreateEventNotice from './components/CreateEventNotice';

/**
 * @since 3.6.0
 */
export default function BlockInspectorControls({attributes, setAttributes}) {
    const {events} = window.eventTicketsBlockSettings;
    const {eventId} = attributes;

    const eventOptions =
        events.map((event) => {
            return {label: event.title, value: `${event.id}`};
        }) ?? [];

    return (
        <InspectorControls>
            <PanelBody title={__('Event', 'give')} initialOpen={true}>
                {events.length === 0 ? (
                    <CreateEventNotice />
                ) : (
                    <PanelRow>
                        <SelectControl
                            label={__('Event Name', 'give')}
                            help={createInterpolateElement(
                                __('Add or edit an event in the <a>events page</a>.', 'give'),
                                {
                                    a: <a href={window.eventTicketsBlockSettings.listEventsUrl} target="_blank" />,
                                }
                            )}
                            value={`${eventId}`}
                            options={[{label: 'Select', value: ''}, ...eventOptions]}
                            onChange={(value: string) => setAttributes({eventId: Number(value)})}
                        />
                    </PanelRow>
                )}
            </PanelBody>
        </InspectorControls>
    );
}
