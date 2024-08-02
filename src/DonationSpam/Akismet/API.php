<?php

namespace Give\DonationSpam\Akismet;

use Akismet;
use Give\DonationSpam\Akismet\DataTransferObjects\CommentCheckArgs;

class API
{
    public function commentCheck(CommentCheckArgs $args)
    {
        // @phpstan-ignore class.notFound
        return Akismet::http_post($args->toHttpQuery(), 'comment-check');
    }
}
