<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

class Name extends Group
{
    const TYPE = 'name';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->append(
            Text::make('firstName')
                ->label('First Name')
                ->required(),

            Text::make('lastName')
                ->label('Last Name'),

            Select::make('honorific')
                ->label('Honorific')
                ->options('Mr.', 'Mrs.', 'Ms.')
        );
    }
}
