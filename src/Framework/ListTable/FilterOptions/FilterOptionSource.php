<?php

namespace Give\Framework\ListTable\FilterOptions;

/**
 * A searchable list of options for one list table filter. An option holds a record id and a plain
 * text label, and nothing else.
 *
 * @since TBD
 */
interface FilterOptionSource
{
    public const PER_PAGE = 20;

    /**
     * Options whose label starts with the search term, or has a word that does, ordered by label then id.
     *
     * @since TBD
     *
     * @return array{options: list<array{value: string, label: string}>, hasMore: bool}
     */
    public function search(string $search, int $page): array;

    /**
     * The labels of these ids only. Ids that do not exist are left out.
     *
     * @since TBD
     *
     * @param int[] $ids
     *
     * @return list<array{value: string, label: string}>
     */
    public function labels(array $ids): array;

    /**
     * @since TBD
     */
    public function currentUserCanView(): bool;
}
