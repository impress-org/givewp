<?php

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Concerns\DefaultValue;
use Give\Framework\ListTable\Concerns\IsFilterable;
use Give\Framework\ListTable\Concerns\IsSortable;
use Give\Framework\ListTable\Concerns\IsVisible;
use Give\Framework\ListTable\Concerns\Label;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * List Table Column class
 *
 * @unreleased
 */
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
     * Create new Column instance
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
