<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

/**
 * @since 3.0.0
 */
class BillingAddress extends Group
{
    const TYPE = 'billingAddress';

    /**
     * @since 3.0.0
     */
    public $apiUrl;

    /**
     * @since 3.0.0
     */
    public $groupLabel;

    /**
     * @since 3.0.0
     */
    public function setApiUrl(string $url): BillingAddress
    {
        $this->apiUrl = $url;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function setGroupLabel(string $groupLabel): BillingAddress
    {
        $this->groupLabel = $groupLabel;

        return $this;
    }

    /**
     * @since 3.0.0
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
