<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased
 */
class BillingAddress extends Group
{
    const TYPE = 'billingAddress';

    /**
     * @unreleased
     */
    public $apiUrl;

    /**
     * @unreleased
     */
    public $groupLabel;

    /**
     * @unreleased
     */
    public function setApiUrl(string $url): BillingAddress
    {
        $this->apiUrl = $url;

        return $this;
    }

    /**
     * @unreleased
     */
    public function setGroupLabel(string $groupLabel): BillingAddress
    {
        $this->groupLabel = $groupLabel;

        return $this;
    }

    /**
     * @unreleased
     *
     * @throws Exceptions\EmptyNameException|Exceptions\NameCollisionException
     */
    public static function make($name): BillingAddress
    {
        return parent::make($name)
            ->append(
                Select::make('country')
                    ->label(__('Country', 'give'))
                    ->options([
                        ['value', 'label'],
                    ]),

                Text::make('address1')
                    ->label(__('Address Line 1', 'give')),

                Text::make('address2')
                    ->label(__('Address Line 2', 'give')),

                Text::make('city')
                    ->label(__('City', 'give')),

                Hidden::make('state')
                    ->label(__('State/Province/Country', 'give')),

                Text::make('zip')
                    ->label(__('Zip/Postal Code', 'give'))
            );
    }
}
