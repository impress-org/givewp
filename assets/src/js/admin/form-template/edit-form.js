/* globals Give, jQuery */
/* eslint-disable */
(function ($) {
    /**
     * Handle form template activation
     * @since: 2.7.0
     */
    const handleFormTemplateActivation = function () {
        $('#form_template_options').on('click', '.js-template--activate', function (ev) {
            ev.preventDefault();

            const $templatesList = $(this).parents('.templates-list'),
                $innerContainer = $templatesList.parent(),
                $parent = $(this).parents('.template-info'),
                activatedTemplateId = $parent.attr('data-id');

            // Deactivate existing activated template.
            $('.template-info', $templatesList).removeClass('active');

            // Show Settings.
            $innerContainer.find(`.template-options.${activatedTemplateId}`).addClass('active');

            $(this).text(Give.fn.getGlobalVar('deactivate'));
            $(this).removeClass('js-template--activate');
            $(this).addClass('js-template--deactivate');

            $(this).parents('.template-info').addClass('active');
            $innerContainer.addClass('has-activated-template');

            $innerContainer.prev('input[name=_give_form_template]').val(activatedTemplateId).trigger('change');
        });
    };

    /**
     * Handle form template deactivation
     * @since: 2.7.0
     */
    const handleFormTemplateDeactivation = function () {
        $('#form_template_options').on('click', '.js-template--deactivate', function (ev) {
            ev.preventDefault();

            const $templatesList = $(this).parents('.templates-list'),
                $innerContainer = $templatesList.parent(),
                $parent = $(this).parents('.template-info'),
                activatedTemplateId = $parent.attr('data-id');

            // Deactivate existing activated template.
            $('.template-info', $templatesList).removeClass('active');

            // Hide Settings.
            $innerContainer.find(`.template-options.${activatedTemplateId}`).removeClass('active');

            $(this).text(Give.fn.getGlobalVar('activate'));
            $(this).removeClass('js-template--deactivate');
            $(this).addClass('js-template--activate');

            $innerContainer.removeClass('has-activated-template');

            $innerContainer.prev('input[name=_give_form_template]').val('').trigger('change');
        });
    };

    /**
     * Handle form template setting vlaidation
     *
     * @since 2.7.0
     */
    const saveFormSettingOnlyIfFormTemplateSelected = function () {
        $('.post-php.post-type-give_forms #publishing-action input[type=submit]')
            .add('.post-new-php.post-type-give_forms #publishing-action input[type=submit]')
            .add('.post-new-php.post-type-give_forms #save-action input[type=submit]')
            .add('.post-php.post-type-give_forms #save-action input[type=submit]')
            .on('click', function () {
                const activatedTemplate = $('input[name=_give_form_template]', '#form_template_options').val();

                if (!activatedTemplate) {
                    new Give.modal.GiveNoticeAlert({
                        type: 'warning',
                        modalContent: {
                            title: Give.fn.getGlobalVar('form_template_required').title,
                            desc: Give.fn.getGlobalVar('form_template_required').desc,
                        },
                    }).render();

                    // Open form template settings.
                    if ('form_template_options' !== Give.fn.getParameterByName('give_tab')) {
                        $('a[href="#form_template_options"]').trigger('click');
                    }

                    return false;
                }

                return true;
            });
    };

    /**
     * Handle conditional form template fields
     *
     * @since 2.7.0
     * @since 2.19.0 Add listener for donation summary fields
     */
    const handleConditionalFormTemplateFields = function () {
        updateIntroductionFields();
        $('input[name="sequoia[introduction][enabled]"]').on('change', function () {
            updateIntroductionFields();
        });

        /**
         * @since 2.19.0
         */
        updateDonationSummaryFields();
        $('input[name="sequoia[payment_information][donation_summary_enabled]"]').on('change', function () {
            updateDonationSummaryFields();
        });

        updateSocialSharingFields();
        $('input[name="sequoia[thank-you][sharing]"]').on('change', function () {
            updateSocialSharingFields();
        });
    };

    /**
     * Update introduction fields
     * Hide or show introduction fields if enabled
     *
     * @since 2.7.0
     */
    const updateIntroductionFields = function () {
        const introductionFields = $(
            '[class*="sequoia[introduction][headline]_field"], [class*="sequoia[introduction][description]_field"], [class*="sequoia[introduction][image]_field"], [class*="sequoia[introduction][donate_label]_field"]'
        );

        if (
            $('input[name="sequoia[introduction][enabled]"]').length !== 0 &&
            !$('input[name="sequoia[introduction][enabled]"]').prop('checked')
        ) {
            $(introductionFields).hide();
        } else {
            $(introductionFields).show();
        }
    };

    /**
     * Update donation summary fields
     * Hide or show fields if enabled
     *
     * @since 2.19.0
     */
    const updateDonationSummaryFields = function () {
        const conditionalFields = $(
            '[class*="sequoia[payment_information][donation_summary_heading]_field"], [class*="sequoia[payment_information][donation_summary_location]_field"]'
        );

        if (
            $('input[name="sequoia[payment_information][donation_summary_enabled]"]').length !== 0 &&
            !$('input[name="sequoia[payment_information][donation_summary_enabled]"]').prop('checked')
        ) {
            $(conditionalFields).hide();
        } else {
            $(conditionalFields).show();
        }
    };

    /**
     * Update social sharing fields
     * Hide or show social sharing fields if enabled
     *
     * @since 2.7.0
     */
    const updateSocialSharingFields = function () {
        const socialSharingFields = $(
            '[class*="sequoia[thank-you][sharing_instruction]_field"], [class*="sequoia[thank-you][twitter_message]_field"]'
        );

        if (
            $('input[name="sequoia[thank-you][sharing]"]').length !== 0 &&
            !$('input[name="sequoia[thank-you][sharing]"]').prop('checked')
        ) {
            $(socialSharingFields).hide();
        } else {
            $(socialSharingFields).show();
        }
    };

    $(document).ready(function () {
        handleFormTemplateActivation();
        handleFormTemplateDeactivation();
        saveFormSettingOnlyIfFormTemplateSelected();
        handleConditionalFormTemplateFields();
    });
})(jQuery);
/* eslint-enable */
