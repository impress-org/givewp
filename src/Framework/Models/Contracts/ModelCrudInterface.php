<?php

namespace Give\Framework\Models\Contracts;

interface ModelCrudInterface {
    public static function find($id);
    public static function create(array $attributes);
    public function save();
    public function delete();
}
