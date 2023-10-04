<?php

namespace Give\DonationForms\Properties;

use Give\DonationForms\FormDesigns\ClassicFormDesign\ClassicFormDesign;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Support\Contracts\Arrayable;
use Give\Framework\Support\Contracts\Jsonable;

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
     * @var string
     */
    public $registration;
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
        $self->goalType = !empty($array['goalType']) && GoalType::isValid($array['goalType']) ? new GoalType(
            $array['goalType']
        ) : GoalType::AMOUNT();
        $self->designId = $array['designId'] ?? null;
        $self->primaryColor = $array['primaryColor'] ?? '#69b86b';
        $self->secondaryColor = $array['secondaryColor'] ?? '#f49420';
        $self->goalAmount = $array['goalAmount'] ?? 0;
        $self->registration = $array['registration'] ?? 'none';
        $self->customCss = $array['customCss'] ?? '';
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
        $self->formStatus = !empty($array['formStatus']) ? new DonationFormStatus(
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
     * @since 3.0.0
     */
    public function toJson($options = 0): string
    {
        return json_encode(
            array_merge(
                $this->toArray(),
                [
                    'goalType' => $this->goalType ? $this->goalType->getValue() : null,
                    'emailTemplateOptions' => array_map([$this, 'addSlashesRecursive'], $this->emailTemplateOptions),
                ]
            )
        );
    }

    public function addSlashesRecursive($value)
    {
        return is_array($value)
            ? array_map([$this, 'addSlashesRecursive'], $value)
            : addslashes($value);
    }
}
