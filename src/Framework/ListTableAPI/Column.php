<?php

namespace Give\Framework\ListTableAPI;

use Give\Framework\ListTableAPI\Concerns\DefaultValue;
use Give\Framework\ListTableAPI\Concerns\IsFilterable;
use Give\Framework\ListTableAPI\Concerns\IsSortable;
use Give\Framework\ListTableAPI\Concerns\IsVisible;
use Give\Framework\ListTableAPI\Concerns\Label;
use Give\Framework\Support\Contracts\Arrayable;

class Column implements Arrayable
{
    use DefaultValue;
    use IsFilterable;
    use IsSortable;
    use IsVisible;
    use Label;

    /**
     * @var string
     */
    protected $id;

    /**
     * @unreleased
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Create new ListTableColumn instance
     *
     * @unreleased
     */
    public static function id(string $id): self
    {
        return new static($id);
    }

    /**
     * @unreleased
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'visible' => $this->visible,
            'sortable' => $this->sortable,
            'filterable' => $this->filterable,
        ];
    }
}
