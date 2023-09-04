<?php

namespace Give\DonationForms\Properties;

use Give\Framework\Support\Contracts\Arrayable;
use Give\Framework\Support\Contracts\Jsonable;

/**
 * @unreleased
 */
class PdfSettings implements Arrayable, Jsonable
{
    /**
     * @var boolean
     */
    public $enable;

    /**
     * @var string
     */
    public $generationMethod;

    /**
     * @unreleased
     */
    public static function fromArray(array $array): self
    {
        $self = new self();

        $self->enable = $array['enable'] === 'enabled';
        $self->generationMethod = $array['generationMethod'] ?? '';

        return $self;
    }

    /**
     * @unreleased
     */
    public static function fromJson(string $json): self
    {
        $self = new self();
        $array = json_decode($json, true);

        return $self::fromArray($array);
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @unreleased
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray());
    }
}
