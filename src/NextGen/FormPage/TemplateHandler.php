<?php

namespace Give\NextGen\FormPage;

use WP_Post as Post;

class TemplateHandler
{
    /**
     * @var Post
     */
    private $post;

    /**
     * @var string
     */
    private $formPageTemplatePath;

    public function __construct( Post $post, string $formPageTemplatePath )
    {
        $this->post = $post;
        $this->formPageTemplatePath = $formPageTemplatePath;
    }

    public function handle($template)
    {
        return $this->isNextGenForm()
            ? $this->formPageTemplatePath
            : $template;
    }

    protected function isNextGenForm(): bool
    {
        return 'give_forms' === $this->post->post_type
               && $this->post->post_content;
    }
}
