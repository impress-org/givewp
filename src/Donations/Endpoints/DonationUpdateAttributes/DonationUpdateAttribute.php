<?php

namespace Give\Donations\Endpoints\DonationUpdateAttributes;

abstract class DonationUpdateAttribute
{
    /**
     * @return string
     */
    abstract public static function getId(): string;

    /**
     * @return array
     */
    abstract public static function getDefinition(): array;
}
