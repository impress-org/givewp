<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

use Give\Framework\Support\Contracts\Arrayable;

/**
 * The statuses of one list table entity: their ids, translated labels, and which of them the Status
 * filter offers. The status badge and the Status filter both read this one list.
 *
 * The list endpoints do not all take statuses the same way. Campaigns takes a `statuses` array of stored
 * values, and keeps `status` as a deprecated string where "any" hides archived campaigns and "inactive" means
 * archived. The other list endpoints take `status` itself as an array. A client has to use the parameter name
 * and the "any" rule of the endpoint it calls.
 *
 * @since TBD
 */
final class StatusVocabulary implements Arrayable
{
    /**
     * @var array<string, string>
     */
    private $labels;

    /**
     * @var string[]
     */
    private $offered;

    /**
     * @since TBD
     *
     * @param array<string, string> $labels  value => translated label, in display order
     * @param string[]              $offered values the Status filter offers, in filter order
     */
    public function __construct(array $labels, array $offered)
    {
        $this->labels = $labels;
        $this->offered = array_values(array_filter($offered, static function ($value) use ($labels): bool {
            return isset($labels[$value]);
        }));
    }

    /**
     * @since TBD
     */
    public function label(string $value): ?string
    {
        return $this->labels[$value] ?? null;
    }

    /**
     * @since TBD
     *
     * @return string[]
     */
    public function offeredValues(): array
    {
        return $this->offered;
    }

    /**
     * Offered statuses first (filter order), then the rest (label order).
     *
     * @since TBD
     *
     * @return array<int, array{value: string, label: string, filterable: bool}>
     */
    public function toArray(): array
    {
        $entries = [];

        foreach ($this->offered as $value) {
            $entries[] = ['value' => (string)$value, 'label' => $this->labels[$value], 'filterable' => true];
        }

        foreach ($this->labels as $value => $label) {
            if (!in_array((string)$value, $this->offered, true)) {
                $entries[] = ['value' => (string)$value, 'label' => $label, 'filterable' => false];
            }
        }

        return $entries;
    }
}
