<?php

namespace Give\EventTickets\Fields;

use Give\Framework\FieldsAPI\Field;

class EventTickets extends Field
{
    protected $title;
    protected $description;
    protected $startDateTime;
    protected $endDateTime;
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
    public function getEndDateTime(): string
    {
        return $this->endDateTime;
    }

    /**
     * @since 3.20.0
     */
    public function endDateTime(string $date): EventTickets
    {
        $this->endDateTime = $date;

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

}
