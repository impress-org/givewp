<?php

namespace Give\Helpers;

/**
 * @unreleased
 *
 * @see https://github.com/jackocnr/intl-tel-input
 */
class IntlTelInput
{
    /**
     * @unreleased
     */
    public static function getCssUrl(): string
    {
        return 'https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.0/build/css/intlTelInput.css';
    }

    /**
     * @unreleased
     */
    public static function getScriptUrl(): string
    {
        return 'https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.0/build/js/intlTelInput.min.js';
    }

    /**
     * @unreleased
     */
    public static function getUtilsScriptUrl(): string
    {
        return 'https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.0/build/js/utils.js';
    }

    /**
     * @unreleased
     */
    public static function getI18n(): array
    {
        $countryList = array_change_key_case(give_get_country_list());
        array_shift($countryList); // Remove first empty item from the country list

        $i18n = array_merge($countryList, [
            // Aria label for the selected country element
            'selectedCountryAriaLabel' => __('Selected country', 'give'),
            // Screen reader text for when no country is selected
            'noCountrySelected' => __('No country selected', 'give'),
            // Aria label for the country list element
            'countryListAriaLabel' => __('List of countries', 'give'),
            // Placeholder for the search input in the dropdown (when countrySearch enabled)
            'searchPlaceholder' => __('Search', 'give'),
            // Screen reader text for when the search produces no results
            'zeroSearchResults' => __('No results found', 'give'),
            // Screen reader text for when the search produces 1 result
            'oneSearchResult' => __('1 result found', 'give'),
            // Screen reader text for when the search produces multiple results, where ${count} will be replaced by the count
            'multipleSearchResults' => __('${count} results found', 'give'),
        ]);

        return $i18n;
    }

    /**
     * @unreleased
     */
    public static function getErrorMap(): array
    {
        return [
            __('Invalid number.', 'give'),
            __('Invalid country code.', 'give'),
            __('Invalid number: too short.', 'give'),
            __('Invalid number: too long.', 'give'),
            __('Invalid number.', 'give'),
        ];
    }

    /**
     * @unreleased
     */
    public static function getInitialCountry(): string
    {
        return strtolower(give_get_country());
    }

    /**
     * @unreleased
     */
    public static function getShowSelectedDialCode(): bool
    {
        return true;
    }

    /**
     * @unreleased
     */
    public static function getStrictMode(): bool
    {
        return true;
    }

    /**
     * @unreleased
     */
    public static function getSettings(): array
    {
        return [
            'initialCountry' => self::getInitialCountry(),
            'showSelectedDialCode' => self::getShowSelectedDialCode(),
            'strictMode' => self::getStrictMode(),
            'i18n' => self::getI18n(),
            'cssUrl' => self::getCssUrl(),
            'scriptUrl' => self::getScriptUrl(),
            'utilsScriptUrl' => self::getUtilsScriptUrl(),
            'errorMap' => self::getErrorMap(),
        ];
    }

    /**
     * @unreleased
     */
    public static function getHtmlInput(string $value, string $id, string $class = '', string $name = ''): string
    {
        if (empty($name)) {
            $name = $id;
        }

        ob_start();

        ?>
        <script src="<?php
        echo self::getScriptUrl(); ?>">
        </script>

        <link rel="stylesheet" href="<?php
        echo self::getCssUrl(); ?>">

        <input id="<?php
        echo $id . '--intl_tel_input'; ?>" class="<?php
        echo $class; ?>" name="<?php
        echo $name; ?>" value="<?php
        echo $value; ?>" type='text'>

        <span id="<?php
        echo $id . '--error-msg'; ?>" class="give-intl-tel-input-hide" style="color:red;"></span>

        <style>
            .give-intl-tel-input-hide {
                display: none !important;
            }

            .give-intl-tel-input-error {
                border: 1px solid red !important;
            }
        </style>
        <script>
            if (document.readyState !== 'loading') {
                readyHandler();
            } else {
                document.addEventListener('DOMContentLoaded', readyHandler);
            }

            function readyHandler() {
                const input = document.querySelector("#<?php echo $id . '--intl_tel_input'; ?>");
                const intl = window.intlTelInput(input, {
                    utilsScript: "<?php echo self::getUtilsScriptUrl(); ?>",
                    hiddenInput: function (telInputName) {
                        return {
                            phone: "<?php echo $id ?>",
                        };
                    },
                    initialCountry: "<?php echo self::getInitialCountry(); ?>",
                    showSelectedDialCode: <?php echo self::getShowSelectedDialCode(); ?>,
                    strictMode: <?php echo self::getStrictMode(); ?>,
                    i18n: <?php echo json_encode(self::getI18n()); ?>
                });

                const errorMsg = document.querySelector("#<?php echo $id . '--error-msg'; ?>");
                const errorMap = <?php echo json_encode(self::getErrorMap()); ?>;

                const resetErrorMessage = () => {
                    input.classList.remove("give-intl-tel-input-error");
                    errorMsg.innerHTML = "";
                    errorMsg.classList.add("give-intl-tel-input-hide");
                };

                const showErrorMessage = (msg) => {
                    input.classList.add("give-intl-tel-input-error");
                    errorMsg.innerHTML = msg;
                    errorMsg.classList.remove("give-intl-tel-input-hide");
                };

                input.addEventListener('change', resetErrorMessage);
                input.addEventListener('keyup', resetErrorMessage);
                input.form.addEventListener("submit", function (e) {
                    if (input.value.trim() && !intl.isValidNumber()) {
                        e.preventDefault();
                        const errorCode = intl.getValidationError();
                        const msg = errorMap[errorCode] || errorMap[0];
                        showErrorMessage(msg);
                        return false;
                    }
                });
            }
        </script>
        <?php

        return ob_get_clean();
    }
}
