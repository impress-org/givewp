<?php

namespace Give\DonationForms\Properties;

use Give\DonationForms\ValueObjects\DesignSettingsImageStyle;
use Give\DonationForms\ValueObjects\DesignSettingsLogoPosition;
use Give\DonationForms\ValueObjects\DesignSettingsSectionStyle;
use Give\DonationForms\ValueObjects\DesignSettingsTextFieldStyle;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\GoalProgressType;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Support\Contracts\Arrayable;
use Give\Framework\Support\Contracts\Jsonable;

/**
 * @since 3.16.0 Added $enableReceiptConfirmationPage property
 * @since 3.12.0 Add goalProgressType
 * @since 3.2.0 Remove addSlashesRecursive method
 * @since 3.0.0
 */
class FormSettings implements Arrayable, Jsonable
{
    /**
     * @var boolean
     */
    public $showHeader;
    /**
     * @var boolean
     */
    public $showHeading;
    /**
     * @var boolean
     */
    public $showDescription;
    /**
     * @var string
     */
    public $formTitle;
    /**
     * @var boolean
     */
    public $enableDonationGoal;
    /**
     * @var boolean
     */
    public $enableAutoClose;
    /**
     * @var GoalType
     */
    public $goalType;
    /**
     * @var GoalProgressType
     */
    public $goalProgressType;
    /**
     * @var string
     */
    public $goalStartDate;
    /**
     * @var string
     */
    public $goalEndDate;
    /**
     * @var string
     */
    public $designId;
    /**
     * @var string
     */
    public $heading;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $primaryColor;
    /**
     * @var string
     */
    public $secondaryColor;
    /**
     * @var float
     */
    public $goalAmount;
    /**
     * @since 3.2.0 Added registrationNotification property.
     * @var string
     */
    public $registrationNotification;
    /**
     * @var string
     */
    public $customCss;
    /**
     * @var string
     */
    public $goalAchievedMessage;

    /**
     * @var string
     */
    public $pageSlug;

    /**
     * @var string
     */
    public $receiptHeading;

    /**
     * @var string
     */
    public $receiptDescription;

    /**
     * @var DonationFormStatus
     */
    public $formStatus;

    /**
     * @var array
     */
    public $emailTemplateOptions;

    /**
     * @var string
     * @todo Extract to a value object.
     */
    public $emailOptionsStatus;

    /**
     * @var string
     */
    public $emailTemplate;

    /**
     * @var string
     */
    public $emailLogo;

    /**
     * @var string
     */
    public $emailFromName;

    /**
     * @var string
     */
    public $emailFromEmail;

    /**
     * @var boolean
     */
    public $formGridCustomize;

    /**
     * @var string
     */
    public $formGridRedirectUrl;

    /**
     * @var string
     */
    public $formGridDonateButtonText;

    /**
     * @var boolean
     */
    public $formGridHideDocumentationLink;

    /**
     * @var boolean
     */
    public $offlineDonationsCustomize;

    /**
     * @var string
     */
    public $offlineDonationsInstructions;

    /**
     * @var string
     */
    public $donateButtonCaption;
    /**
     * @var string
     */
    public $multiStepFirstButtonText;
    /**
     * @var string
     */
    public $multiStepNextButtonText;

    /**
     * @var array
     */
    public $pdfSettings;

    /**
     * @since 3.4.0
     * @var string
     */
    public $designSettingsImageUrl;

    /**
     * @since 3.11.0
     * @var string
     */
    public $designSettingsImageAlt;

    /**
     * @since 3.4.0
     * @var string
     */
    public $designSettingsImageStyle;

    /**
     * @since 3.4.0
     * @var string
     */
    public $designSettingsLogoUrl;

    /**
     * @since 3.4.0
     * @var string
     */
    public $designSettingsLogoPosition;

    /**
     * @since 3.4.0
     * @var string
     */
    public $designSettingsSectionStyle;

    /**
     * @since 3.4.0
     * @var string
     */
    public $designSettingsTextFieldStyle;

    /**
     * @since 3.6.0
     * @var string
     */
    public $designSettingsImageColor;

    /**
     * @since 3.6.0
     * @var string
     */
    public $designSettingsImageOpacity;

    /**
     * @since 3.7.0
     * @var string
     */
    public $formExcerpt;

    /**
     * @since 3.9.0
     * @var array
     */
    public $currencySwitcherSettings;
    /**
     * @since 3.16.0
     * @var bool
     */
    public $enableReceiptConfirmationPage;

    /**
     * @since 3.16.0 Added $enableReceiptConfirmationPage
     * @since 3.7.0 Added formExcerpt
     * @since 3.11.0 Sanitize customCSS property
     * @since 3.2.0 Added registrationNotification
     * @since 3.0.0
     */
    public static function fromArray(array $array): self
    {
        $self = new self();

        $self->showHeader = $array['showHeader'] ?? true;
        $self->showHeading = $array['showHeading'] ?? true;
        $self->heading = $array['heading'] ?? __('Support Our Cause', 'give');
        $self->showDescription = $array['showDescription'] ?? true;
        $self->description = $array['description'] ?? __(
            'Help our organization by donating today! Donations go to making a difference for our cause.',
            'give'
        );
        $self->formTitle = $array['formTitle'] ?? __('Donation Form', 'give');
        $self->donateButtonCaption = $array['donateButtonCaption'] ?? __('Donate now', 'give');
        $self->enableDonationGoal = $array['enableDonationGoal'] ?? false;
        $self->enableAutoClose = $array['enableAutoClose'] ?? false;
        $self->goalType = ! empty($array['goalType']) && GoalType::isValid($array['goalType']) ? new GoalType(
            $array['goalType']
        ) : GoalType::AMOUNT();
        $self->goalProgressType = ! empty($array['goalProgressType']) && GoalProgressType::isValid($array['goalProgressType'])
            ? new GoalProgressType($array['goalProgressType'])
            : GoalProgressType::ALL_TIME();
        $self->goalStartDate = $array['goalStartDate'] ?? '';
        $self->goalEndDate = $array['goalEndDate'] ?? '';
        $self->designId = $array['designId'] ?? null;
        $self->primaryColor = $array['primaryColor'] ?? '#69b86b';
        $self->secondaryColor = $array['secondaryColor'] ?? '#f49420';
        $self->goalAmount = $array['goalAmount'] ?? 0;
        $self->registrationNotification = $array['registrationNotification'] ?? false;
        $self->customCss = wp_strip_all_tags($array['customCss'] ?? '');
        $self->pageSlug = $array['pageSlug'] ?? '';
        $self->goalAchievedMessage = $array['goalAchievedMessage'] ?? __(
            'Thank you to all our donors, we have met our fundraising goal.',
            'give'
        );
        $self->receiptHeading = $array['receiptHeading'] ?? __(
            'Hey {first_name}, thanks for your donation!',
            'give'
        );
        $self->receiptDescription = $array['receiptDescription'] ?? __(
            '{first_name}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {email}.',
            'give'
        );

        $self->enableReceiptConfirmationPage = $array['enableReceiptConfirmationPage'] ?? false;

        $self->formStatus = ! empty($array['formStatus']) ? new DonationFormStatus(
            $array['formStatus']
        ) : DonationFormStatus::DRAFT();

        $self->formGridCustomize = $array['formGridCustomize'] ?? false;
        $self->formGridRedirectUrl = $array['formGridRedirectUrl'] ?? '';
        $self->formGridDonateButtonText = $array['formGridDonateButtonText'] ?? '';
        $self->formGridHideDocumentationLink = $array['formGridHideDocumentationLink'] ?? false;

        $self->emailTemplateOptions = $array['emailTemplateOptions'] ?? [];

        $self->emailOptionsStatus = $array['emailOptionsStatus'] ?? 'global';

        $self->emailTemplate = $array['emailTemplate'] ?? 'default';

        $self->emailFromName = $array['emailFromName'] ?? '';

        $self->emailFromEmail = $array['emailFromEmail'] ?? '';

        $self->emailLogo = $array['emailLogo'] ?? '';

        $self->offlineDonationsCustomize = $array['offlineDonationsCustomize'] ?? false;

        $self->offlineDonationsInstructions = $array['offlineDonationsInstructions'] ?? '';

        $self->multiStepFirstButtonText = $array['multiStepFirstButtonText'] ?? __('Donate now', 'give');

        $self->multiStepNextButtonText = $array['multiStepNextButtonText'] ?? __('Continue', 'give');

        $self->pdfSettings = isset($array['pdfSettings']) && is_array(
            $array['pdfSettings']
        ) ? $array['pdfSettings'] : [];

        $self->designSettingsImageUrl = $array['designSettingsImageUrl'] ?? '';
        $self->designSettingsImageAlt = $array['designSettingsImageAlt'] ?? $self->formTitle;
        $self->designSettingsImageStyle = ! empty($array['designSettingsImageStyle']) ? new DesignSettingsImageStyle(
            $array['designSettingsImageStyle']
        ) : DesignSettingsImageStyle::BACKGROUND();

        $self->designSettingsLogoUrl = $array['designSettingsLogoUrl'] ?? '';
        $self->designSettingsLogoPosition = ! empty($array['designSettingsLogoPosition']) ? new DesignSettingsLogoPosition(
            $array['designSettingsLogoPosition']
        ) : DesignSettingsLogoPosition::LEFT();

        $self->designSettingsSectionStyle = ! empty($array['designSettingsSectionStyle']) ? new DesignSettingsSectionStyle(
            $array['designSettingsSectionStyle']
        ) : DesignSettingsSectionStyle::DEFAULT();

        $self->designSettingsTextFieldStyle = ! empty($array['designSettingsTextFieldStyle']) ? new DesignSettingsTextFieldStyle(
            $array['designSettingsTextFieldStyle']
        ) : DesignSettingsTextFieldStyle::DEFAULT();

        $self->designSettingsImageColor = $array['designSettingsImageColor'] ?? '';

        $self->designSettingsImageOpacity = $array['designSettingsImageOpacity'] ?? '';

        $self->formExcerpt = $array['formExcerpt'] ?? '';

        $self->currencySwitcherSettings = isset($array['currencySwitcherSettings']) && is_array(
            $array['currencySwitcherSettings']
        ) ? $array['currencySwitcherSettings'] : [];

        return $self;
    }

    /**
     * @since 3.0.0
     */
    public static function fromJson(string $json): self
    {
        return self::fromArray(
            json_decode($json, true)
        );
    }

    /**
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @since 3.2.0 Remove call to addSlashesRecursive method for emailTemplateOptions in favor of SanitizeDonationFormPreviewRequest class
     * @since 3.0.0
     */
    public function toJson($options = 0): string
    {
        return json_encode(
            array_merge(
                $this->toArray(),
                [
                    'goalType' => $this->goalType ? $this->goalType->getValue() : null,
                ]
            )
        );
    }
}
