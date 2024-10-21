<?php

namespace Give\EventTickets\Fields;

use Give\Framework\FieldsAPI\Field;

class EventTickets extends Field
{
    protected $title;
    protected $startDateTime;
    protected $description;
    protected $ticketTypes = [];

    const TYPE = 'eventTickets';

    /**
     * @since 3.6.0
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @since 3.6.0
     */
    public function title(string $title): EventTickets
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @since 3.6.0
     */
    public function getStartDateTime(): string
    {
        return $this->startDateTime;
    }

    /**
     * @since 3.6.0
     */
    public function startDateTime(string $date): EventTickets
    {
        $this->startDateTime = $date;
        return $this;
    }

    /**
     * @since 3.6.0
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @since 3.6.0
     */
    public function description(string $description): EventTickets
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @since 3.6.0
     */
    public function getTicketTypes(): array
    {
        return $this->ticketTypes;
    }

    /**
     * @since 3.6.0
     */
    public function ticketTypes(array $ticketTypes): EventTickets
    {
        $this->ticketTypes = $ticketTypes;
        return $this;
    }

    /**
     * @since 3.6.0
     */
    public function getTicketsLabel(): string
    {
        return apply_filters(
            'givewp_event_tickets_block/tickets_label',
            __('Select Tickets', 'give')
        );
    }

    /**
     * @since 3.6.0
     */
    public function getSoldOutMessage(): string
    {
        return apply_filters(
            'givewp_event_tickets_block/sold_out_message',
            __(
                'Thank you for supporting our cause. Our fundraising event tickets are officially sold out. You can still contribute by making a donation.',
                'give'
            )
        );
    }
}
