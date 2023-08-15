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
     * @since 0.5.0
     */
    public function getUseGlobalSettings(): bool
    {
        return $this->useGlobalSettings;
    }

    /**
     * @since 0.5.0
     */
    public function useGlobalSettings(bool $useGlobalSettings): Consent
    {
        $this->useGlobalSettings = $useGlobalSettings;

        return $this;
    }

    /**
     * @since 0.5.0
     */
    public function getCheckboxLabel(): string
    {
        return $this->checkboxLabel;
    }

    /**
     * @since 0.5.0
     */
    public function checkboxLabel(string $text): Consent
    {
        $this->checkboxLabel = $text;

        return $this;
    }

    /**
     * @since 0.5.0
     */
    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    /**
     * @since 0.5.0
     */
    public function displayType(string $text): Consent
    {
        $this->displayType = $text;

        return $this;
    }

    /**
     * @since 0.5.0
     */
    public function getLinkText(): string
    {
        return $this->linkText;
    }

    /**
     * @since 0.5.0
     */
    public function linkText(string $text): Consent
    {
        $this->linkText = $text;

        return $this;
    }

    /**
     * @since 0.5.0
     */
    public function getLinkUrl(): string
    {
        return $this->linkUrl;
    }

    /**
     * @since 0.5.0
     */
    public function linkUrl(string $url): Consent
    {
        $this->linkUrl = $url;

        return $this;
    }

    /**
     * @since 0.5.0
     */
    public function getModalHeading(): string
    {
        return $this->modalHeading;
    }

    /**
     * @since 0.5.0
     */
    public function modalHeading(string $text): Consent
    {
        $this->modalHeading = $text;

        return $this;
    }

    /**
     * @since 0.5.0
     */
    public function getModalAcceptanceText(): string
    {
        return $this->modalAcceptanceText;
    }

    /**
     * @since 0.5.0
     */
    public function modalAcceptanceText(string $text): Consent
    {
        $this->modalAcceptanceText = $text;

        return $this;
    }

    /**
     * @since 0.5.0
     */
    public function getAgreementText(): string
    {
        return $this->agreementText;
    }

    /**
     * @since 0.5.0
     */
    public function agreementText(string $text): Consent
    {
        $this->agreementText = $text;

        return $this;
    }

}
