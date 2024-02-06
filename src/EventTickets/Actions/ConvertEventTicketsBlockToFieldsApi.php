<?php
namespace Give\EventTickets\Actions;

use Give\Donations\Models\Donation;
use Give\EventTickets\Fields\EventTickets;
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
        return EventTickets::make('eventTickets')
            ->tap(function (EventTickets $eventTicketsField) use ($block, $formId) {

                $eventTicketsField
                    ->title('Event 1')
                    ->date('2024-01-10 10:00')
                    ->description('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.')
                    ->tickets([
                        [
                            'id' => 1,
                            'name' => 'Standard',
                            'price' => 50,
                            'quantity' => 5,
                            'description' => 'Standard ticket description goes here',
                        ],
                        [
                            'id' => 2,
                            'name' => 'VIP',
                            'price' => 100,
                            'quantity' => 5,
                            'description' => 'VIP ticket description goes here',
                        ],
                    ]);

                $eventTicketsField->scope(function (EventTickets $field, $value, Donation $donation) {
                    if (empty($value)) {
                        return;
                    }

                    // (new UpdateDonationWithEventTickets())($value, $donation, $field);
                });

                return $eventTicketsField;
            });
    }
}
