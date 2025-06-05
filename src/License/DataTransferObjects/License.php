<?php

namespace Give\License\DataTransferObjects;

/**
 * @since 4.3.0
 */
class License
{
    public bool $isActive;
    public float $gatewayFee;
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

    /**
     * @since 4.3.0
     */
    public static function fromData(array $data): self
    {
        $self = new self();
        $self->isActive = $data['license'] === 'valid';
        $self->success = (bool)($data['success'] ?? false);
        $self->license = (string)($data['license'] ?? '');
        $self->itemId = $data['item_id'] ?? null;
        $self->itemName = (string)($data['item_name'] ?? '');
        $self->checksum = (string)($data['checksum'] ?? '');
        $self->expires = (string)($data['expires'] ?? '');
        $self->paymentId = (int)($data['payment_id'] ?? 0);
        $self->customerName = (string)($data['customer_name'] ?? '');
        $self->customerEmail = (string)($data['customer_email'] ?? '');
        $self->licenseLimit = (int)($data['license_limit'] ?? 1);
        $self->siteCount = (int)$data['site_count'];
        $self->activationsLeft = (int)($data['activations_left'] ?? 0);
        $self->priceId = $data['price_id'];
        $self->licenseKey = (string)($data['license_key'] ?? '');
        $self->licenseId = (int)($data['license_id'] ?? 0);
        $self->isAllAccessPass = (bool)filter_var($data['is_all_access_pass'] ?? null, FILTER_VALIDATE_BOOLEAN);
        $self->gatewayFee = (float)($data['gateway_fee'] ?? 0);

        if (is_array($data['download'])) {
              foreach ($data['download'] as $downloadData) {
                $self->downloads[] = Download::fromData($downloadData);
            }
        } elseif (is_string($data['download'])) {
            $self->downloads[] = Download::fromData([
                'file' => $data['download'],
                'plugin_slug' => $data['plugin_slug'] ?? '',
                'readme' => $data['readme'] ?? '',
                'current_version' => $data['current_version'] ?? '',
            ]);
        }

        return $self;
    }
}
