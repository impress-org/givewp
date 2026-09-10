<?php

namespace Give\Tests\Unit\EventTickets\Actions;

use DateTime;
use Exception;
use Give\Donations\Models\Donation;
use Give\DonationForms\Models\DonationForm;
use Give\EventTickets\Actions\ConvertEventTicketsBlockToFieldsApi;
use Give\EventTickets\DataTransferObjects\EventTicketTypeData;
use Give\EventTickets\Fields\EventTickets;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.20.0
 */
class ConvertEventTicketsBlockToFieldsApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.20.0
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

        $this->assertEquals('event-tickets', $field->getName());
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

    /**
     * @since 4.16.8.1
     * @throws EmptyNameException
     * @throws Exception
     */
    public function testScopeCallbackMintsATicketForTheBlocksOwnEvent(): void
    {
        $event = $this->makeActiveEvent();
        $ticketType = EventTicketType::factory()->create([
            'eventId' => $event->id,
            'price' => new Money(1000, 'USD'),
            'capacity' => 5,
        ]);

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

        $donation = Donation::factory()->create(['amount' => new Money(1000, 'USD')]);

        $field->getScopeCallback()(
            $field,
            wp_json_encode([['ticketId' => $ticketType->id, 'quantity' => 1]]),
            $donation
        );

        $this->assertSame(1, give(EventTicketRepository::class)->queryByTicketTypeId($ticketType->id)->count());
        $this->assertEquals(2000, $donation->amount->getAmount());
    }

    /**
     * @since 4.16.8.1
     * @throws EmptyNameException
     * @throws Exception
     */
    public function testScopeCallbackRejectsATicketTypeFromAnUnrelatedEvent(): void
    {
        $ownEvent = $this->makeActiveEvent();
        $otherEventTicketType = EventTicketType::factory()->create(['eventId' => $this->makeActiveEvent()->id]);

        $block = BlockModel::make([
            'name' => 'givewp/event-tickets',
            'attributes' => [
                'eventId' => $ownEvent->id,
            ],
        ]);

        $donationForm = DonationForm::factory()->create();

        $action = give(ConvertEventTicketsBlockToFieldsApi::class);
        /** @var EventTickets $field */
        $field = $action($block, $donationForm->id);

        $donation = Donation::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $field->getScopeCallback()(
            $field,
            wp_json_encode([['ticketId' => $otherEventTicketType->id, 'quantity' => 1]]),
            $donation
        );
    }

    /**
     * @since 4.16.8.1
     * @throws EmptyNameException
     * @throws Exception
     */
    public function testScopeCallbackRejectsATicketWhenTheEventHasAlreadyEnded(): void
    {
        $event = Event::factory()->create([
            'startDateTime' => new DateTime('-3 days'),
            'endDateTime' => new DateTime('-2 days'),
        ]);
        $ticketType = EventTicketType::factory()->create(['eventId' => $event->id]);

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

        $donation = Donation::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $field->getScopeCallback()(
            $field,
            wp_json_encode([['ticketId' => $ticketType->id, 'quantity' => 1]]),
            $donation
        );
    }

    /**
     * EventFactory's own default startDateTime (`dateTimeThisYear('+6 months')`) can land in the
     * past relative to "now", which would make the ended-event check reject it — a real event
     * date, not a bug in the check. Tests that aren't specifically about the ended-event
     * condition need a guaranteed-future event instead of the shared factory default.
     *
     * @since 4.16.8.1
     */
    private function makeActiveEvent(): Event
    {
        return Event::factory()->create([
            'startDateTime' => new DateTime('+1 day'),
            'endDateTime' => new DateTime('+2 days'),
        ]);
    }
}
