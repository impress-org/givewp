<?php

namespace Give\NextGen\Framework\TemplateTags\Actions;

class TransformTemplateTags {
    /**
     * @unreleased
     */
    public function __invoke(string $content, array $tags): string
    {
        return str_replace(
            array_keys($tags),
            array_values($tags),
            $content
        );
    }
}