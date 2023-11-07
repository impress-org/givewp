/*!
 * Give Admin Forms JS
 *
 * @description: The Give Admin Settings scripts. Only enqueued on the give-settings page; used for tabs and other show/hide functionality
 * @package:     Give
 * @since:       1.5
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, GiveWP
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* globals Give, jQuery, givePayPalCommerce, ajaxurl */

jQuery(document).ready(function ($) {
    /**
     *  Sortable payment gateways.
     */
    const $payment_gateways = jQuery('ul.give-payment-gatways-list');
    if ($payment_gateways.length) {
        $payment_gateways.sortable();
    }

    /**
     * Change currency position symbol on changing the currency
     */
    const give_settings_currency = '#give-mainform #currency';
    const give_settings_position = '#give-mainform #currency_position';
    $('body').on('change', give_settings_currency, function () {
        const $currency = $(give_settings_currency + ' option:selected'),
            currencyCode = $currency.val(),
            currencyList = JSON.parse($(this).attr('data-formatting-setting'));

        let beforeText = (afterText = {}),
            formattingSetting = currencyList[currencyCode],
            $thounsandSeparator = $('#thousands_separator', '#give-mainform'),
            $decimalSeparator = $('#decimal_separator', '#give-mainform'),
            $numerDecimals = $('#number_decimals', '#give-mainform');

        // Change currency position text.
        beforeText = $(give_settings_position)
            .data('before-template')
            .replace('{currency_pos}', formattingSetting.symbol);
        $(give_settings_position + ' option[value="before"]').text(beforeText);

        afterText = $(give_settings_position)
            .data('after-template')
            .replace('{currency_pos}', formattingSetting.symbol);
        $(give_settings_position + ' option[value="after"]').text(afterText);

        // Change thousand separator.
        $thounsandSeparator.val(formattingSetting.setting['thousands_separator']).trigger('blur');

        // Change decimal separator.
        $decimalSeparator.val(formattingSetting.setting['decimal_separator']).trigger('blur');

        // Change number of decimals.
        $numerDecimals.val(formattingSetting.setting['number_decimals']).trigger('blur');
    });

    /**
     * Repeater setting field event.
     */
    $('a.give-repeat-setting-field').on('click', function (e) {
        e.preventDefault();
        let $parent = $(this).parents('td'),
            $first_setting_field_group = $('p:first-child', $parent),
            $new_setting_field_group = $first_setting_field_group.clone(),
            setting_field_count = $('p', $parent).not('.give-field-description').length,
            fieldID = $(this).data('id') + '_' + ++setting_field_count,
            $prev_field = $(this).prev();

        // Create new field only if previous is non empty.
        if ($('input', $prev_field).val()) {
            // Add setting field html to dom.
            $(this).before($new_setting_field_group);
            $prev_field = $(this).prev();

            // Set id and value for setting field.
            $('input', $prev_field).attr('id', fieldID);
            $('input', $prev_field).val('');
        }

        return false;
    });

    $('.give-settings-page').on('click', 'span.give-remove-setting-field', function (e) {
        $(this).parents('p').remove();
    });

    /**
     * Enabled & disable email notification event.
     */
    $('.give-email-notification-status', 'table.giveemailnotifications').on('click', function () {
        const $this = $(this),
            $icon_container = $('i', $this),
            $loader = $(this).next(),
            set_notification_status = $(this).hasClass('give-email-notification-enabled') ? 'disabled' : 'enabled',
            notification_id = $(this).data('id'),
            canEditEmailNotificationStatus = parseInt($this.data('edit'));

        if (!canEditEmailNotificationStatus) {
            showEmailNotificationStatusIsNotEditableNotice($this);
            return false;
        }

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'give_set_notification_status',
                status: set_notification_status,
                notification_id: notification_id,
            },
            beforeSend: function () {
                $this.hide();
                $loader.addClass('is-active');
            },
            success: function (res) {
                if (res.success) {
                    $this.removeClass('give-email-notification-' + $this.data('status'));
                    $this.addClass('give-email-notification-' + set_notification_status);
                    $this.data('status', set_notification_status);

                    if ('enabled' === set_notification_status) {
                        $icon_container.removeClass('dashicons-no-alt');
                        $icon_container.addClass('dashicons-yes');
                    } else {
                        $icon_container.removeClass('dashicons-yes');
                        $icon_container.addClass('dashicons-no-alt');
                    }

                    $loader.removeClass('is-active');
                    $this.show();
                }
            },
        });
    });

    /**
     * Ajax call to clear Give's cache.
     */
    $('#give-clear-cache').on('click', function () {
        /**
         * @since 2.25.2 add nonce to ajax request.
         */
        const nonce = document.getElementById('give_cache_flush_nonce').value;

        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'give_cache_flush',
                _ajax_nonce: nonce,
            },
        }).done(function (response) {
            if (response.success) {
                new Give.modal.GiveSuccessAlert({
                    modalContent: {
                        title: Give.fn.getGlobalVar('flush_success'),
                        desc: response.data.message,
                        cancelBtnTitle: Give.fn.getGlobalVar('ok'),
                    },
                }).render();
            } else {
                new Give.modal.GiveErrorAlert({
                    modalContent: {
                        title: Give.fn.getGlobalVar('flush_error'),
                        desc: response.data.message,
                        cancelBtnTitle: Give.fn.getGlobalVar('ok'),
                    },
                }).render();
            }
        });
    });

    let dTemp = Give.fn.getGlobalVar('decimal_separator'), // Temporary variable to store decimal separator.
        tTemp = Give.fn.getGlobalVar('thousands_separator'), // Temporary variable to store thousand separator.
        symbolRegex = /\(([^)]+)\)/, // Regex to extract currency symbol.
        formatterArgs = {
            position: Give.fn.getGlobalVar('currency_pos'),
            symbol: Give.fn.getGlobalVar('currency_sign'),
            precision: Give.fn.getGlobalVar('number_decimals'),
            decimal: Give.fn.getGlobalVar('decimal_separator'),
            thousand: Give.fn.getGlobalVar('thousands_separator'),
        }; // Object argument required to format the amount.

    /**
     * Logic to show Currency Preview.
     *
     * The variables above are part of the below code which should lie outside the code below.
     */
    $('#number_decimals, #decimal_separator, #thousands_separator, #currency_position, #currency').on(
        'input blur change',
        function (e) {
            const preview = $('#currency_preview'),
                dSeparator = $('#decimal_separator'),
                tSeparator = $('#thousands_separator'),
                targetName = e.target.name,
                targetValue = e.target.value;

            /**
             * Sets the precision (number of decimals) for the formatted amount.
             *
             */
            if ('number_decimals' === targetName && ('input' === e.type || 'blur' === e.type)) {
                if ((!targetValue || isNaN(targetValue)) && 'blur' === e.type) {
                    e.target.value = Give.fn.getGlobalVar('number_decimals');
                }
                /**
                 * Checks if the input is a number, will set to '0' if otherwise or
                 * if the input is left empty.
                 */
                formatterArgs.precision = isNaN(parseInt(targetValue))
                    ? Give.fn.getGlobalVar('number_decimals')
                    : parseInt(targetValue);
            }

            /**
             * The next 2 sections are for the decimal separator and thousand separator.
             * if the decimal separator === thousand separator, then the values are swapped.
             */
            if ('decimal_separator' === targetName && ('input' === e.type || 'blur' === e.type)) {
                if (!targetValue && 'blur' === e.type) {
                    e.target.value = dTemp;
                }
                formatterArgs.decimal = targetValue;
                /**
                 * Logic for swapping decimal separator with thousand separator if both
                 * are the same value.
                 */
                if (formatterArgs.hasOwnProperty('thousand') && 'input' === e.type) {
                    if (formatterArgs.decimal === formatterArgs.thousand) {
                        formatterArgs.thousand = dTemp;
                        tSeparator.val(dTemp);
                        dTemp = targetValue;
                        tTemp = tSeparator.val();
                    }
                } else if (
                    formatterArgs.decimal === Give.fn.getGlobalVar('thousands_separator') &&
                    'input' === e.type
                ) {
                    formatterArgs.thousand = dTemp;
                    tSeparator.val(dTemp);
                    dTemp = targetValue;
                    tTemp = tSeparator.val();
                }
            }

            if ('thousands_separator' === targetName && ('input' === e.type || 'blur' === e.type)) {
                if (!targetValue && 'blur' === e.type) {
                    e.target.value = tTemp;
                }
                formatterArgs.thousand = targetValue;
                /**
                 * Logic for swapping decimal separator with thousand separator if both
                 * are the same value.
                 */
                if (formatterArgs.hasOwnProperty('decimal') && 'input' === e.type) {
                    if (formatterArgs.decimal === formatterArgs.thousand) {
                        formatterArgs.decimal = tTemp;
                        dSeparator.val(tTemp);
                        tTemp = targetValue;
                        dTemp = dSeparator.val();
                    }
                } else if (formatterArgs.thousand === Give.fn.getGlobalVar('decimal_separator') && 'input' === e.type) {
                    formatterArgs.decimal = tTemp;
                    dSeparator.val(tTemp);
                    tTemp = targetValue;
                    dTemp = dSeparator.val();
                }
            }

            /**
             * Sets the currency position: Before | After
             */
            if ('currency_position' === targetName && 'change' === e.type) {
                formatterArgs.position = targetValue;
            }

            /**
             * Sets the currency and the symbol.
             */
            if ('currency' === targetName && 'change' === e.type) {
                formatterArgs.currency = targetValue;
                const matched = symbolRegex.exec(e.target[e.target.selectedIndex].text);
                formatterArgs.symbol = matched[1];
            }

            preview.val(Give.fn.formatCurrency('123456.12345', formatterArgs, {}));
        }
    );

    /**
     * Show admin notice if email notification status is not editable.
     *
     * @since 2.14.0
     * @param {object} noticeEditButton
     * @return {boolean}
     */
    function showEmailNotificationStatusIsNotEditableNotice(noticeEditButton) {
        $('div.give-email-notification-status-notice').remove();

        // Add notice.
        $('.wp-heading-inline').after(
            `<div class="updated error give-email-notification-status-notice"><p>${noticeEditButton.data(
                'notice'
            )}</p></div>`
        );

        // Scroll to notice.
        let noticeContainer = $('div.give-email-notification-status-notice');
        if (noticeContainer.length) {
            $('html, body').animate({scrollTop: noticeContainer.position().top}, 'slow');
        }
    }
});

// Vertical tabs feature.
document.addEventListener('DOMContentLoaded', () => {
    const mainContentWrap = document.querySelector('.give-settings-section-content');

    // Bailout, if main content wrap not exists.
    if (null === mainContentWrap) {
        return;
    }

    const menuContentWrap = mainContentWrap.querySelector('.give-settings-section-group-menu');

    // Bailout, if menu content wrap not exists.
    if (null === menuContentWrap) {
        return;
    }

    const allContent = Array.prototype.slice.call(mainContentWrap.querySelectorAll('.give-settings-section-group'));

    const menuButtons = Array.from(menuContentWrap.querySelectorAll('ul li a')).concat(
        Array.from(mainContentWrap.querySelectorAll('ul.give-subsubsub li a'))
    );

    // Bailout, if menu content wrap not exists.
    if (null === menuButtons) {
        return;
    }

    menuButtons.forEach((element) => {
        element.addEventListener('click', (e) => {
            let targetElement = e.target;

            if (targetElement.tagName !== 'A') {
                targetElement = targetElement.closest('a');

                if (!targetElement) {
                    return;
                }
            }
            const hasSubGroup = targetElement.hasAttribute('data-subgroup');
            let selectedGroup, selectedContent;

            if (hasSubGroup) {
                const menuContainer = targetElement.parentElement.parentElement;
                const sectionGroup = menuContainer.parentElement;
                selectedGroup = targetElement.getAttribute('data-subgroup');
                selectedContent = mainContentWrap.querySelector(`#give-settings-section-subgroup-${selectedGroup}`);

                // Loop through menu button and remove `current` class.
                menuContainer.querySelectorAll('a').forEach((element) => {
                    element.classList.remove('current');
                });

                // Loop through content sections and add `give-hidden` class.
                sectionGroup
                    .querySelectorAll('.give-settings-section-subgroup ')
                    .forEach((contentElement) => contentElement.classList.add('give-hidden'));

                // Add `active` class to menu buttons of selected element.
                targetElement.classList.add('current');
            } else {
                selectedGroup = targetElement.getAttribute('data-group');
                selectedContent = mainContentWrap.querySelector(`#give-settings-section-group-${selectedGroup}`);

                // Loop through menu button and remove `active` class.
                menuButtons.forEach((element) => {
                    element.classList.remove('active');
                });

                // Loop through content sections and add `give-hidden` class.
                allContent.map((contentElement) => contentElement.classList.add('give-hidden'));

                // Add `active` class to menu buttons of selected element.
                targetElement.classList.add('active');
            }

            // Remove `give-hidden` class from content section of selected element.
            selectedContent.classList.remove('give-hidden');

            // Update URL in browser address without reloading the page.
            history.pushState({urlPath: targetElement.getAttribute('href')}, '', targetElement.getAttribute('href'));

            // Don't redirect the page.
            e.preventDefault();
            return false;
        });
    });
});

// Payment Gateways Settings dialog
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('give-payment-gateway-settings-dialog');

    if (dialog === null) {
        return;
    }

    Array.from(
        dialog.querySelectorAll(
            '#give-payment-gateway-settings-dialog__close, .give-payment-gateway-settings-dialog__content-button'
        )
    ).forEach((element) => {
        element.addEventListener('click', (e) => {
            e.preventDefault();
            dialog.close();
        });
    });

    dialog.showModal();
});
