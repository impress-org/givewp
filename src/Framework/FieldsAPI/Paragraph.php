<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

class Paragraph extends Element
{
    const TYPE = 'paragraph';

    protected $content = '';

    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
