<?php

namespace Give\PaymentGateways\TheGivingBlock\DataTransferObjects;

/**
 * @unreleased
 */
class Organization
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $logo;

    /**
     * @var string
     */
    public $country;

    /**
     * @var bool
     */
    public $allowsAnon;

    /**
     * @var string
     */
    public $nonprofitTaxID;

    /**
     * @var bool
     */
    public $areNotesEnabled;

    /**
     * @var bool
     */
    public $isReceiptEnabled;

    /**
     * @var string
     */
    public $createdAt;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $city;

    /**
     * @var string
     */
    public $postcode;

    /**
     * @var string
     */
    public $nonprofitAddress1;

    /**
     * @var string
     */
    public $nonprofitAddress2;

    /**
     * @var string
     */
    public $uuid;

    /**
     * @var bool
     */
    public $areFiatDonationsEnabled;

    /**
     * @var bool
     */
    public $areCryptoDonationsEnabled;

    /**
     * @var bool
     */
    public $areStockDonationsEnabled;

    /**
     * @var bool
     */
    public $areDafDonationsEnabled;

    /**
     * @var bool
     */
    public $areTributesEnabled;

    /**
     * @var string
     */
    public $organizationType;

    /**
     * @var bool
     */
    public $isWebsiteProfileVisible;

    /**
     * @var bool
     */
    public $isSupportPanelEnabled;

    /**
     * @var string
     */
    public $customSupportEmail;

    /**
     * @var array
     */
    public $categories;

    /**
     * @var array
     */
    public $widgetCode;

    /**
     * @var array
     */
    public $websiteBlocks;

    /**
     * @var array|null
     */
    public $fundsDesignations;

    /**
     * @var int
     */
    public $shift4ApiVersion;

    /**
     * @unreleased
     */
    public static function fromOptions(): Organization
    {
        $instance = new self();

        $organizationData = get_option('give_tgb_organization', []);

        if (empty($organizationData)) {
            return $instance;
        }

        $instance->id = $organizationData['id'] ?? '';
        $instance->name = $organizationData['name'] ?? '';
        $instance->logo = $organizationData['logo'] ?? '';
        $instance->country = $organizationData['country'] ?? '';
        $instance->allowsAnon = $organizationData['allowsAnon'] ?? false;
        $instance->nonprofitTaxID = $organizationData['nonprofitTaxID'] ?? '';
        $instance->areNotesEnabled = $organizationData['areNotesEnabled'] ?? false;
        $instance->isReceiptEnabled = $organizationData['isReceiptEnabled'] ?? false;
        $instance->createdAt = $organizationData['createdAt'] ?? '';
        $instance->state = $organizationData['state'] ?? '';
        $instance->city = $organizationData['city'] ?? '';
        $instance->postcode = $organizationData['postcode'] ?? '';
        $instance->nonprofitAddress1 = $organizationData['nonprofitAddress1'] ?? '';
        $instance->nonprofitAddress2 = $organizationData['nonprofitAddress2'] ?? '';
        $instance->uuid = $organizationData['uuid'] ?? '';
        $instance->areFiatDonationsEnabled = $organizationData['areFiatDonationsEnabled'] ?? false;
        $instance->areCryptoDonationsEnabled = $organizationData['areCryptoDonationsEnabled'] ?? false;
        $instance->areStockDonationsEnabled = $organizationData['areStockDonationsEnabled'] ?? false;
        $instance->areDafDonationsEnabled = $organizationData['areDafDonationsEnabled'] ?? false;
        $instance->areTributesEnabled = $organizationData['areTributesEnabled'] ?? false;
        $instance->organizationType = $organizationData['organizationType'] ?? '';
        $instance->isWebsiteProfileVisible = $organizationData['isWebsiteProfileVisible'] ?? false;
        $instance->isSupportPanelEnabled = $organizationData['isSupportPanelEnabled'] ?? false;
        $instance->customSupportEmail = $organizationData['customSupportEmail'] ?? '';
        $instance->categories = $organizationData['categories'] ?? [];
        $instance->widgetCode = $organizationData['widgetCode'] ?? [];
        $instance->websiteBlocks = $organizationData['websiteBlocks'] ?? [];
        $instance->fundsDesignations = $organizationData['fundsDesignations'] ?? null;
        $instance->shift4ApiVersion = $organizationData['shift4ApiVersion'] ?? 1;

        return $instance;
    }

    /**
     * @unreleased
     */
    public static function fromApiResponse(array $organizationData): Organization
    {
        $instance = new self();

        $instance->id = $organizationData['id'] ?? '';
        $instance->name = $organizationData['name'] ?? '';
        $instance->logo = $organizationData['logo'] ?? '';
        $instance->country = $organizationData['country'] ?? '';
        $instance->allowsAnon = $organizationData['allowsAnon'] ?? false;
        $instance->nonprofitTaxID = $organizationData['nonprofitTaxID'] ?? '';
        $instance->areNotesEnabled = $organizationData['areNotesEnabled'] ?? false;
        $instance->isReceiptEnabled = $organizationData['isReceiptEnabled'] ?? false;
        $instance->createdAt = $organizationData['createdAt'] ?? '';
        $instance->state = $organizationData['state'] ?? '';
        $instance->city = $organizationData['city'] ?? '';
        $instance->postcode = $organizationData['postcode'] ?? '';
        $instance->nonprofitAddress1 = $organizationData['nonprofitAddress1'] ?? '';
        $instance->nonprofitAddress2 = $organizationData['nonprofitAddress2'] ?? '';
        $instance->uuid = $organizationData['uuid'] ?? '';
        $instance->areFiatDonationsEnabled = $organizationData['areFiatDonationsEnabled'] ?? false;
        $instance->areCryptoDonationsEnabled = $organizationData['areCryptoDonationsEnabled'] ?? false;
        $instance->areStockDonationsEnabled = $organizationData['areStockDonationsEnabled'] ?? false;
        $instance->areDafDonationsEnabled = $organizationData['areDafDonationsEnabled'] ?? false;
        $instance->areTributesEnabled = $organizationData['areTributesEnabled'] ?? false;
        $instance->organizationType = $organizationData['organizationType'] ?? '';
        $instance->isWebsiteProfileVisible = $organizationData['isWebsiteProfileVisible'] ?? false;
        $instance->isSupportPanelEnabled = $organizationData['isSupportPanelEnabled'] ?? false;
        $instance->customSupportEmail = $organizationData['customSupportEmail'] ?? '';
        $instance->categories = $organizationData['categories'] ?? [];
        $instance->widgetCode = $organizationData['widgetCode'] ?? [];
        $instance->websiteBlocks = $organizationData['websiteBlocks'] ?? [];
        $instance->fundsDesignations = $organizationData['fundsDesignations'] ?? null;
        $instance->shift4ApiVersion = $organizationData['shift4ApiVersion'] ?? 1;

        return $instance;
    }
}
