<?php

namespace Give\Framework\Models\Contracts;

use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;

/**
 * @since 2.19.6
 */
interface ModelCrud
{
    /**
     * @since 2.19.6
     *
     * @param  int  $id
     * @return Model
     */
    public static function find($id);

    /**
     * @since 2.19.6
     *
     * @param  array  $attributes
     * @return Model
     */
    public static function create(array $attributes);

    /**
     * @since 2.19.6
     *
     * @return Model
     */
    public function save();

    /**
     * @since 2.19.6
     *
     * @return bool
     */
    public function delete();

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder
     */
    public static function query();

    /**
     * @since 2.19.6
     *
     * @param $object
     * @return Model
     */
    public static function fromQueryBuilderObject($object);
}
