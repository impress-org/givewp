<?php

namespace Give\EventTickets\Actions;

use Give\Donations\Models\Donation;
use Give\EventTickets\DataTransferObjects\TicketPurchaseData;
use Give\EventTickets\DataTransferObjects\EventTicketTypeData;
use Give\EventTickets\Fields\EventTickets;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;
use Give\EventTickets\Repositories\EventRepository;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;

class ConvertEventTicketsBlockToFieldsApi
{
    /**
     * @unreleased
     *
     * @throws EmptyNameException
     */
    public function __invoke(BlockModel $block, int $formId)
    {
        return EventTickets::make($block->getShortName() . '-' . $block->getAttribute('eventId'))
            ->tap(function (EventTickets $eventTicketsField) use ($block, $formId) {
                $eventId = $block->getAttribute('eventId');
                $event = give(EventRepository::class)->getById($eventId);
                $ticketTypes = array_map(function ($ticketType) {
                    return EventTicketTypeData::make($ticketType)->toArray();
                }, $event->ticketTypes()->getAll() ?? []);

                $eventTicketsField
                    ->title($event->title)
                    ->startDateTime($event->startDateTime->format('Y-m-d H:i:s'))
                    ->description($event->description)
                    ->ticketTypes($ticketTypes);

                $eventTicketsField->scope(function (EventTickets $field, $value, Donation $donation) {

                    $ticketPurchaseData = array_map(function ($data) {
                        return TicketPurchaseData::fromFieldValueObject($data);
                    }, json_decode($value));

                    array_walk($ticketPurchaseData, new GenerateTicketsFromPurchaseData($donation));
                });

                return $eventTicketsField;
            });
    }
}