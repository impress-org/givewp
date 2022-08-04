<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

interface ColumnInterface
{
    /**
     * @unreleased
     */
    public function getId(): string;

    /**
     * @unreleased
     */
    public function getLabel(): string;
}
