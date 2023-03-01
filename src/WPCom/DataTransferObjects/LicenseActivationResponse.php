<?php

namespace Give\WPCom\DataTransferObjects;

class LicenseActivationResponse
{
    /**
     * @var int
     */
    public $siteCount;

    /**
     * @var int
     */
    public $activationsLeft;

    /**
     * @var bool
     */
    public $success;

    /**
     * @unreleased
     */
    public static function fromArray(array $data): self
    {
        $response = new self();
        $response->siteCount = (int)$data['site_count'];
        $response->activationsLeft = (int)$data['activations_left'];
        $response->success = (bool)$data['success'];

        return $response;
    }
}
