<?php

namespace Give\Framework\TemplateTags\Actions;

class TransformTemplateTags
{
    /**
     * @since 3.0.0
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