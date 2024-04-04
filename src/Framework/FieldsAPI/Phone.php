<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unlreased add phone format attribute
 * @since 2.32.0 added description
 * @since 2.12.0
 * @since 2.14.0 add min/max length validation
 */
class Phone extends Field
{
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasMaxLength;
    use Concerns\HasMinLength;
    use Concerns\HasPlaceholder;
    use Concerns\HasDescription;

    const TYPE = 'phone';

    /** @var string */
    protected $phoneFormat = '';

    /** @var string */
    protected $intlTelInputSettings = [];

    /** @var string */
    protected $intlTelInputFullNumber = '';

    /** @var string */
    protected $intlTelInputCountryCode = '';

    /**
     * Set the phone format for the element.
     *
     * @since 3.0.0
     */
    public function phoneFormat(string $phoneFormat): self
    {
        $this->phoneFormat = $phoneFormat;

        return $this;
    }

    /**
     * Set the intl-tel-input options for the element.
     *
     * @see https://github.com/jackocnr/intl-tel-input
     *
     * @unreleased
     */
    public function setIntlTelInputSettings(array $intlTelInputSettings): self
    {
        $this->intlTelInputSettings = $intlTelInputSettings;

        return $this;
    }

    /**
     * @unreleased
     */
    public function setIntlTelInputFullNumber(string $intlTelInputFullNumber): self
    {
        $this->intlTelInputFullNumber = $intlTelInputFullNumber;

        return $this;
    }

    /**
     * @unreleased
     */
    public function setIntlTelInputCountryCode(string $intlTelInputCountryCode): self
    {
        $this->intlTelInputCountryCode = $intlTelInputCountryCode;

        return $this;
    }

    /**
     * Get the phone format for the element.
     *
     * @since 3.0.0
     */
    public function getPhoneFormat(): string
    {
        return $this->phoneFormat;
    }

    /**
     * @unreleased
     *
     * @throws Exceptions\EmptyNameException
     */
    /*public static function make($name): Field
    {
        return parent::make($name)->setIntlTelInputSettings([
            'initialCountry' => strtolower(give_get_country()),
            'i18n' => give_get_intl_tel_input_i18n_json_object(),
            'cssUrl' => give_get_intl_tel_input_css_url(),
            'scriptUrl' => give_get_intl_tel_input_script_url(),
            'utilsScriptUrl' => give_get_intl_tel_input_utils_script_url(),
        ]);
    }*/
}
