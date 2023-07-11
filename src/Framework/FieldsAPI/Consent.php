<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

class Consent extends field
{
    use Concerns\HasLabel;
    
    protected $useGlobalSettings;
    protected $checkboxLabel;
    protected $displayType;
    protected $linkText;
    protected $linkUrl;
    protected $modalHeading;
    protected $modalAcceptanceText;
    protected $agreementText;

    const TYPE = 'consent';

    /**
     * @unreleased
     */
    public function getUseGlobalSettings(): bool
    {
        return $this->useGlobalSettings;
    }

    /**
     * @unreleased
     */
    public function useGlobalSettings(bool $useGlobalSettings): Consent
    {
        $this->useGlobalSettings = $useGlobalSettings;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getCheckboxLabel(): string
    {
        return $this->checkboxLabel;
    }

    /**
     * @unreleased
     */
    public function checkboxLabel(string $text): Consent
    {
        $this->checkboxLabel = $text;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    /**
     * @unreleased
     */
    public function displayType(string $text): Consent
    {
        $this->displayType = $text;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getLinkText(): string
    {
        return $this->linkText;
    }

    /**
     * @unreleased
     */
    public function linkText(string $text): Consent
    {
        $this->linkText = $text;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getLinkUrl(): string
    {
        return $this->linkUrl;
    }

    /**
     * @unreleased
     */
    public function linkUrl(string $url): Consent
    {
        $this->linkUrl = $url;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getModalHeading(): string
    {
        return $this->modalHeading;
    }

    /**
     * @unreleased
     */
    public function modalHeading(string $text): Consent
    {
        $this->modalHeading = $text;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getModalAcceptanceText(): string
    {
        return $this->modalAcceptanceText;
    }

    /**
     * @unreleased
     */
    public function modalAcceptanceText(string $text): Consent
    {
        $this->modalAcceptanceText = $text;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getAgreementText(): string
    {
        return $this->agreementText;
    }

    /**
     * @unreleased
     */
    public function agreementText(string $text): Consent
    {
        $this->agreementText = $text;

        return $this;
    }

}
