<?php

namespace Give\Framework\ListTable\FilterOptions;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\WhereQueryBuilder;

/**
 * Shared query code for the option sources. A source only says which table and columns to read.
 *
 * Each query reads the id and the label column and nothing else, so no other field can be returned.
 * A page is one query that asks for one row more than the page size, to tell if there is a next page.
 *
 * @since TBD
 */
abstract class AbstractFilterOptionSource implements FilterOptionSource
{
    /**
     * A query on the source table, limited to the records the filter may list.
     *
     * @since TBD
     */
    abstract protected function query(): QueryBuilder;

    /**
     * @since TBD
     */
    abstract protected function idColumn(): string;

    /**
     * @since TBD
     */
    abstract protected function labelColumn(): string;

    /**
     * @since TBD
     */
    protected function optionLabel(string $label, object $row): string
    {
        return html_entity_decode($label, ENT_QUOTES | ENT_HTML5);
    }

    /**
     * @since TBD
     */
    public function search(string $search, int $page): array
    {
        $label = $this->labelColumn();
        $query = $this->selectColumns($this->query());

        if ($search !== '') {
            $term = DB::esc_like($search);
            // A title saved without `unfiltered_html` is stored encoded ("Tom &amp; Jerry"), and the option shows it decoded.
            $encodedTerm = DB::esc_like(esc_html($search));

            $query->where(static function (WhereQueryBuilder $builder) use ($label, $term, $encodedTerm) {
                $builder->whereLike($label, $term . '%')->orWhereLike($label, '% ' . $term . '%');

                if ($encodedTerm !== $term) {
                    $builder->orWhereLike($label, $encodedTerm . '%')->orWhereLike($label, '% ' . $encodedTerm . '%');
                }
            });
        }

        $rows = $query
            ->orderBy($label)
            ->orderBy($this->idColumn())
            ->limit(self::PER_PAGE + 1)
            ->offset((max($page, 1) - 1) * self::PER_PAGE)
            ->getAll();

        $rows = $rows ?: [];

        return [
            'options' => $this->toOptions(array_slice($rows, 0, self::PER_PAGE)),
            'hasMore' => count($rows) > self::PER_PAGE,
        ];
    }

    /**
     * @since TBD
     */
    public function labels(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function (int $id) {
            return $id > 0;
        })));

        if (!$ids) {
            return [];
        }

        $rows = $this->selectColumns($this->query())
            ->whereIn($this->idColumn(), $ids)
            ->orderBy($this->labelColumn())
            ->orderBy($this->idColumn())
            ->getAll();

        return $this->toOptions($rows ?: []);
    }

    /**
     * @since TBD
     */
    protected function selectColumns(QueryBuilder $query): QueryBuilder
    {
        return $query->select([$this->idColumn(), 'id'], [$this->labelColumn(), 'label']);
    }

    /**
     * @since TBD
     *
     * @param object[] $rows
     *
     * @return list<array{value: string, label: string}>
     */
    private function toOptions(array $rows): array
    {
        return array_map(function ($row) {
            return [
                'value' => (string)$row->id,
                'label' => $this->optionLabel((string)$row->label, $row),
            ];
        }, $rows);
    }
}
