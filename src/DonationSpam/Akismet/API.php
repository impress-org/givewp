<?php

namespace Give\DonationSpam\Akismet;

use Akismet;
use Give\DonationSpam\Akismet\DataTransferObjects\CommentCheckArgs;

/**
 * @since 3.15.0
 */
class API
{
    /**
     * @since 3.15.0
     */
    public function commentCheck(CommentCheckArgs $args): array
    {
        // @phpstan-ignore class.notFound
        return Akismet::http_post($args->toHttpQuery(), 'comment-check');
    }
}
