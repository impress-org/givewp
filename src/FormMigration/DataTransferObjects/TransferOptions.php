<?php

namespace Give\FormMigration\DataTransferObjects;

use WP_REST_Request;

class TransferOptions
{
    /** @var string */
    protected $changeUrl;

    /** @var bool */
    protected $delete;

    /** @var bool */
    protected $redirect;

    public function __construct(bool $changeUrl, bool $delete, bool $redirect)
    {
        $this->changeUrl = $changeUrl;
        $this->delete = $delete;
        $this->redirect = $redirect;
    }

    public static function fromRequest(WP_REST_Request $request): TransferOptions
    {
        return new TransferOptions(
            $request->get_param('changeUrl'),
            $request->get_param('delete'),
            $request->get_param('redirect')
        );
    }

    public static function fromArray($options): TransferOptions
    {
        return new TransferOptions(
            $options['changeUrl'],
            $options['delete'],
            $options['redirect']
        );
    }

    public function shouldChangeUrl(): bool
    {
        return $this->changeUrl;
    }

    public function shouldDelete(): bool
    {
        return $this->delete;
    }

    public function shouldRedirect(): bool
    {
        return $this->redirect;
    }
}
