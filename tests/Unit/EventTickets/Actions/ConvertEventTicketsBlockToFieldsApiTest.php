<?php

namespace Give\Tests\Unit\EventTickets\Actions;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\EventTickets\Actions\ConvertEventTicketsBlockToFieldsApi;
use Give\EventTickets\DataTransferObjects\EventTicketTypeData;
use Give\EventTickets\Fields\EventTickets;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class ConvertEventTicketsBlockToFieldsApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @throws EmptyNameException
     * @throws Exception
     */
    public function testBlockToFieldConversionMatchesAttributes(): void
    {
        $ticketType = EventTicketType::factory()->create();
        $event = $ticketType->event;

        $block = BlockModel::make([
            'name' => 'givewp/event-tickets',
            'attributes' => [
                'eventId' => $event->id,
            ],
        ]);

        $donationForm = DonationForm::factory()->create();

        $action = give(ConvertEventTicketsBlockToFieldsApi::class);
        /** @var EventTickets $field */
        $field = $action($block, $donationForm->id);

        $this->assertEquals('event-tickets-1', $field->getName());
        $this->assertEquals('eventTickets', $field->getType());
        $this->assertEquals('field', $field->getNodeType());

        $expectedAttributes = [
            'title' => $event->title,
            'startDateTime' => $event->startDateTime->format('Y-m-d H:i:s'),
            'endDateTime' => $event->endDateTime->format('Y-m-d H:i:s'),
            'description' => $event->description,
            'ticketTypes' => [EventTicketTypeData::make($ticketType)->toArray()],
        ];

        $fieldAttributes = $field->jsonSerialize();

        foreach ($expectedAttributes as $key => $value) {
            $this->assertEquals($value, $fieldAttributes[$key]);
        }
    }
}
