<?php

namespace Give\DonationSpam\Akismet;

use Akismet;
use Give\DonationSpam\Akismet\DataTransferObjects\CommentCheckArgs;

/**
 * @unreleased
 */
class API
{
    /**
     * @unreleased
     */
    public function commentCheck(CommentCheckArgs $args): array
    {
        // @phpstan-ignore class.notFound
        return Akismet::http_post($args->toHttpQuery(), 'comment-check');
    }
}
