import {InspectorControls} from '@wordpress/block-editor';
import {PanelBody, PanelRow, SelectControl, TextareaControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import CreateEventNotice from './components/CreateEventNotice';

/**
 * @unreleased
 */
export default function BlockInspectorControls({attributes, setAttributes}) {
    const {events} = window.eventTicketsBlockSettings;
    const {eventId, ticketsLabel, ticketsSoldOutMessage} = attributes;

    const eventOptions =
        events.map((event) => {
            return {label: event.title, value: event.id};
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
                            help={__('Add or edit an event in the events page.', 'give')}
                            value={eventId}
                            options={[{label: 'Select', value: ''}, ...eventOptions]}
                            onChange={(value: string) => setAttributes({eventId: Number(value)})}
                        />
                    </PanelRow>
                )}
            </PanelBody>
            {events.length === 0 && eventId && (
                <PanelBody title={__('Tickets', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextControl
                            label={__('Label', 'give')}
                            value={ticketsLabel}
                            onChange={(value) => setAttributes({ticketsLabel: value})}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextareaControl
                            label={__('Sold Out Message', 'give')}
                            value={ticketsSoldOutMessage}
                            onChange={(value) => setAttributes({ticketsSoldOutMessage: value})}
                        />
                    </PanelRow>
                </PanelBody>
            )}
        </InspectorControls>
    );
}
