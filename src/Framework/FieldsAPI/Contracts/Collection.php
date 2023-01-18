<?php

namespace Give\Framework\FieldsAPI\Contracts;

use Give\Framework\FieldsAPI\Field;

interface Collection
{
    /**
     * @since 2.10.2
     *
     * Fluently append nodes to the collection.
     *
     * @return $this do not add return type until PHP 7.4 is minimum
     */
    public function append(Node ...$nodes);

    /**
     * Fluently remove a named node.
     *
     * @since 2.10.2
     *
     * @return mixed
     */
    public function remove(string $name);

    /**
     * Get all the nodes.
     *
     * @since 2.10.2
     *
     * @return Node[]
     */
    public function all(): array;

    /**
     * Count all the nodes.
     *
     * @since 2.10.2
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get a node’s index by its name.
     *
     * @since 2.10.2
     *
     * @return int|null
     */
    public function getNodeIndexByName(string $name);

    /**
     * Get a node by its name.
     *
     * @since 2.10.2
     *
     * @return Node|Collection
     */
    public function getNodeByName(string $name);

    /**
     * Get only the field nodes.
     *
     * @return Field[]
     */
    public function getFields(): array;

    /**
     * Inserts the given noe after the node with the given name.
     *
     * @since 2.10.2
     *
     * @return $this
     */
    public function insertAfter(string $siblingName, Node $node);

    /**
     * Inserts the given noe before the node with the given name.
     *
     * @since 2.10.2
     *
     * @return $this
     */
    public function insertBefore(string $siblingName, Node $node);

    /**
     * Walk through each node in the collection
     *
     * @since 2.10.2
     *
     * @return void
     */
    public function walk(callable $callback);
}
