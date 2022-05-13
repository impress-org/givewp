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
    protected $name;

    /**
     * @unreleased
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Create new Column instance
     *
     * @unreleased
     */
    public static function name(string $name): self
    {
        return new static($name);
    }

    /**
     * @unreleased
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'text' => $this->getText(),
            'visible' => $this->isVisible(),
            'sortable' => $this->isSortable(),
            'filterable' => $this->isFilterable(),
        ];
    }
}
