<?php

namespace Give\DonationForms\FormPage;

use WP_Post as Post;

class TemplateHandler
{
    /**
     * @var Post|null
     */
    private $post;

    /**
     * @var string
     */
    private $formPageTemplatePath;

    public function __construct( $post, string $formPageTemplatePath )
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
        return $this->post
               && $this->post->post_content
               &&'give_forms' === $this->post->post_type;
    }
}
