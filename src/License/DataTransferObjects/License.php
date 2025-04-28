<?php

namespace Give\License\DataTransferObjects;

/**
 * @unreleased
 */
class License
{
    public bool $success;
    public string $license;
    public ?int $itemId; // false or int
    public string $itemName;
    public string $checksum;
    public string $expires;
    public int $paymentId;
    public string $customerName;
    public string $customerEmail;
    public int $licenseLimit;
    public int $siteCount;
    public int $activationsLeft;
    public ?int $priceId; // false or int
    public string $licenseKey;
    public int $licenseId;
    public bool $isAllAccessPass;
    /** @var Download[] */
    public array $downloads = [];
    public bool $isValid;
    public int $gatewayFee;

    /**
     * @unreleased
     */
    public static function fromData(array $data): self
    {
        $self = new self();
        $self->isValid = $data['license'] === 'valid';
        $self->success = (bool)$data['success'];
        $self->license = (string)$data['license'];
        $self->itemId = $data['item_id'];
        $self->itemName = (string)$data['item_name'];
        $self->checksum = (string)$data['checksum'];
        $self->expires = (string)$data['expires'];
        $self->paymentId = (int)$data['payment_id'];
        $self->customerName = (string)$data['customer_name'];
        $self->customerEmail = (string)$data['customer_email'];
        $self->licenseLimit = (int)$data['license_limit'];
        $self->siteCount = (int)$data['site_count'];
        $self->activationsLeft = (int)$data['activations_left'];
        $self->priceId = $data['price_id'];
        $self->licenseKey = (string)$data['license_key'];
        $self->licenseId = (int)$data['license_id'];
        $self->isAllAccessPass = (bool)filter_var($data['is_all_access_pass'] ?? null, FILTER_VALIDATE_BOOLEAN);
        $self->gatewayFee = (int)($data['gateway_fee'] ?? 0);

        foreach ($data['download'] as $downloadData) {
            $self->downloads[] = Download::fromData($downloadData);
        }

        return $self;
    }
}
