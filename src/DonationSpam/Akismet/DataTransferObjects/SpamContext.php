<?php

namespace Give\DonationSpam\Akismet\DataTransferObjects;

class SpamContext
{
    /**
     * @var CommentCheckArgs
     */
    protected $args;

    /**
     * @var array
     */
    protected $response;

    public function __construct(CommentCheckArgs $args, array $response)
    {
        $this->args = $args;
        $this->response = $response;
    }

    public function __serialize(): array
    {
        return [
            'donor_email' => $this->args->comment_author_email,
            'filter'      => 'akismet',
            'message'     => $this->formatMessage(),
        ];
    }

    public function formatMessage(): string
    {
        return sprintf(
            '<p><strong>%1$s</strong><pre>%2$s</pre></p><strong>%3$s</strong><pre>%4$s</pre><p>',
            __( 'Request', 'give' ),
            print_r( $this->args, true ),
            __( 'Response', 'give' ),
            print_r( $this->response, true )
        );
    }
}
