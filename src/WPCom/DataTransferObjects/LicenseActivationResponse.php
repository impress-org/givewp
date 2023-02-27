<?php

namespace DataTransferObjects;

class LicenseActivationResponse
{
    /**
     * @var string
     */
    public $licenseKey;

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
    public $isAllAccessPass;

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
        $response->licenseKey = $data['license_key'];
        $response->siteCount = (int)$data['site_count'];
        $response->activationsLeft = (int)$data['activations_left'];
        $response->isAllAccessPass = (bool)$data['is_all_access_pass'];
        $response->success = (bool)$data['success'];

        return $response;
    }
}
