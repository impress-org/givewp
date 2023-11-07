/**
 * Give Admin JS
 *
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, GiveWP
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/* globals Give, jQuery */
import {GiveConfirmModal, GiveErrorAlert, GiveWarningAlert} from '../plugins/modal';
import {GiveShortcodeButton} from './shortcode-button.js';
import setupChosen from './utils/setupChosen';
import accounting from 'accounting';

// Provided access to global level.
let give_setting_edit = false;
const gravatar = require('gravatar');

(function ($) {
    /**
     * Show/Hide ajax loader.
     *
     * @since 2.0
     *
     * @param $parent
     * @param args
     */
    const giveAjaxLoader = function ($parent, args) {
        args = jQuery.extend(
            {
                wrapper: true,
                show: false,
            },
            args
        );

        const $loaderParent = args.wrapper ? $('.give-spinner-wrapper', $parent) : {},
            $loader = $('.give-spinner', $parent);

        // Show loader.
        if (args.show) {
            if ($loaderParent.length) {
                $loaderParent.addClass('is-active');
            }

            $loader.addClass('is-active');
            return;
        }

        // Hide loader.
        if ($loaderParent.length) {
            $loaderParent.removeClass('is-active');
        }

        $loader.removeClass('is-active');
    };

    /**
     * Onclick remove give-message parameter from url
     *
     * @since 1.8.14
     */
    const give_dismiss_notice = function () {
        $('body').on('click', 'button.notice-dismiss', function () {
            if ('give-invalid-license' !== jQuery(this).closest('div.give-notice').data('notice-id')) {
                give_remove_give_message(jQuery(this).closest('div.give-notice').attr('id'));
            }
        });
    };

    /**
     * Remove give-message parameter from URL.
     *
     * @since 1.8.14
     * @since 2.1.4 Added new param key which remove the multiple message array from URL.
     *
     * @param key string to remove from url in multiple notices.
     */
    var give_remove_give_message = function (key) {
        var parameter = 'give-message',
            url = document.location.href,
            urlparts = url.split('?'),
            key = undefined === key ? '' : key.replace('give-', '');

        if (urlparts.length >= 2) {
            const urlBase = urlparts.shift();
            const queryString = urlparts.join('?');
            const prefix = encodeURIComponent(parameter) + '=';

            const pars = queryString.split(/[&;]/g);
            for (let i = pars.length; i-- > 0; ) {
                if (
                    pars[i].lastIndexOf(prefix, 0) !== -1 ||
                    ('' !== key && pars[i].lastIndexOf('give-messages', 0) !== -1 && pars[i].match(key + '$'))
                ) {
                    pars.splice(i, 1);
                }
            }
            url = urlBase + '?' + pars.join('&');
            window.history.pushState('', document.title, url); // added this line to push the new url directly to url bar .
        }
        return url;
    };

    /**
     * Setup Admin Datepicker
     * @since: 1.0
     */
    const enable_admin_datepicker = function () {
        let datepicker = $('.give_datepicker'),
            inputDefaultDate;

        if (datepicker.length) {
            let $clone = {},
                options = {
                    altFormat: 'yy-mm-dd',
                    onClose: function (selectedDate, inst) {
                        if (!selectedDate.length) {
                            inst.input.next().val('');
                        }
                    },
                };

            $.each(datepicker, function (index, $input) {
                $input = $($input);
                inputDefaultDate =
                    undefined !== $input.attr('data-standard-date')
                        ? $input.attr('data-standard-date')
                        : $input.attr('value');

                if (!$input.attr('name').length) {
                    return;
                }

                $clone = $input.clone();

                // Update datepicker list with latest.
                datepicker[index] = $clone;

                $clone.attr('name', '');

                $input.before($clone);
                $input.hide();
                $input.attr('class', '');
                $input.attr('id', '');
                $input.val(inputDefaultDate);
                $input.prop('readonly', true);
            });

            if (datepicker.length > 0) {
                $.each(datepicker, function (index, $input) {
                    $input = $($input);
                    options.altField = $input.next();

                    $input.datepicker(options);
                });
            }
        }
    };

    /**
     * Setup Pretty Chosen Select Fields
     */
    const setup_chosen_give_selects = function () {
        // Setup Chosen Selects.
        const $give_chosen_containers = $('.give-select-chosen');

        if ($give_chosen_containers.hasClass('give-chosen-settings')) {
            // Do something to chosen used in metabox or admin settings.
            $give_chosen_containers
                .chosen({
                    no_results_text: Give.fn.getGlobalVar('chosen_add_title_prefix') + ' ',
                    width: '30%',
                })
                .on('chosen:no_results', function (evt, data) {
                    $(data.chosen.container).on('keydown', function (event) {
                        const chosenText = data.chosen.get_search_text(),
                            $selectField = jQuery(data.chosen.form_field);

                        if (
                            13 === event.keyCode &&
                            !$selectField.find('option[value="' + chosenText + '"]').length &&
                            'true' === $selectField.attr('data-allows-new-values')
                        ) {
                            $(data.chosen.form_field)
                                .append('<option value="' + chosenText + '" selected>' + chosenText + '</option>')
                                .trigger('chosen:updated');
                            data.chosen.result_highlight =
                                data.chosen.search_results.find('li.active-result').lasteturn;
                            data.chosen.result_select(evt);
                        }
                    });
                });
        } else {
            setupChosen($give_chosen_containers);
        }

        // Fix: Chosen JS - Zero Width Issue.
        // @see https://github.com/harvesthq/chosen/issues/472#issuecomment-344414059
        $('.chosen-container').each(function () {
            if (0 === $(this).width()) {
                $(this).css('width', '100%');
            }
        });

        // This fixes the Chosen box being 0px wide when the thickbox is opened.
        $('#post').on('click', '.give-thickbox', function () {
            $('.give-select-chosen', '#choose-give-form').css('width', '100%');
        });
    };

    /**
     * Unformat Currency
     *
     * @param   {string}      price Price
     * @param   {number|bool} dp    Number of decimals
     *
     * @returns {string}
     */
    function give_unformat_currency(price, dp) {
        price = accounting.unformat(price, Give.fn.getGlobalVar('decimal_separator')).toString();
        dp = 'undefined' === dp ? false : dp;

        // Set default value for number of decimals.
        if (false !== dp) {
            price = parseFloat(price).toFixed(dp);
        } else {
            // If price do not have decimal value then set default number of decimals.
            price = parseFloat(price).toFixed(Give.fn.getGlobalVar('currency_decimals'));
        }

        return price;
    }

    /**
     * List donation screen JS
     */
    const GiveListDonation = {
        init: function () {
            this.deleteSingleDonation();
            this.resendSingleDonationReceipt();
        },

        deleteSingleDonation: function () {
            new GiveConfirmModal({
                triggerSelector: '.delete-single-donation',
                modalWrapper: 'give-modal--warning',
                modalContent: {
                    title: Give.fn.getGlobalVar('confirm_delete_donation'),
                    desc: Give.fn.getGlobalVar('delete_payment'),
                },
                successConfirm: function (args) {
                    window.location.assign(args.el.attr('href'));
                },
            });
        },

        resendSingleDonationReceipt: function () {
            new GiveConfirmModal({
                triggerSelector: '.resend-single-donation-receipt',
                modalContent: {
                    title: Give.fn.getGlobalVar('confirm_resend'),
                    desc: Give.fn.getGlobalVar('resend_receipt'),
                },
                successConfirm: function (args) {
                    window.location.assign(args.el.attr('href'));
                },
            });
        },
    };

    /**
     * Edit donation screen JS
     */
    const Give_Edit_Donation = {
        init: function () {
            this.edit_address();
            this.add_note();
            this.remove_note();
            this.new_donor();
            this.resend_receipt();
            this.variable_price_list();
        },

        edit_address: function () {
            // Update base state field based on selected base country.
            $('select[name="give-payment-address[0][country]"]').change(function () {
                const $this = $(this),
                    data = {
                        action: 'give_get_states',
                        country: $this.val(),
                        field_name: 'give-payment-address[0][state]',
                    };

                $.post(ajaxurl, data, function (response) {
                    // Show the states dropdown menu.
                    $this
                        .closest('.column-container')
                        .find('#give-order-address-state-wrap')
                        .removeClass('give-hidden');

                    // Add support to zip fields.
                    $this.closest('.column-container').find('.give-column').removeClass('column-full');
                    $this.closest('.column-container').find('.give-column').addClass('column');

                    const state_wrap = $('#give-order-address-state-wrap');
                    state_wrap.find('*').not('.order-data-address-line').remove();
                    if (typeof response.states_found !== undefined && true === response.states_found) {
                        state_wrap.append(response.data);
                        state_wrap.find('select').chosen();
                    } else {
                        state_wrap.append(
                            '<input type="text" name="give-payment-address[0][state]" value="' +
                                response.default_state +
                                '" class="give-edit-toggles medium-text"/>'
                        );

                        if (typeof response.show_field !== undefined && false === response.show_field) {
                            // Hide the states dropdown menu.
                            $this
                                .closest('.column-container')
                                .find('#give-order-address-state-wrap')
                                .addClass('give-hidden');

                            // Add support to zip fields.
                            $this.closest('.column-container').find('.give-column').addClass('column-full');
                            $this.closest('.column-container').find('.give-column').removeClass('column');
                        }
                    }
                });

                return false;
            });
        },

        add_note: function () {
            $('#give-add-payment-note').on('click', function (e) {
                e.preventDefault();

                // ajax function to save donation note
                function save_note() {
                    $.ajax({
                        type: 'POST',
                        data: postData,
                        url: ajaxurl,
                        beforeSend: function () {
                            noteContainer.prop('disabled', true);
                            $this.prop('disabled', true);
                        },
                        success: function (response) {
                            $('#give-payment-notes-inner').append(response);
                            $('.give-no-payment-notes').hide();
                            $('#give-payment-note').val('');
                        },
                    })
                        .fail(function (data) {
                            if (window.console && window.console.log) {
                                console.log(data);
                            }
                        })
                        .always(function () {
                            noteContainer.prop('disabled', false);
                            $this.prop('disabled', false);
                        });
                }

                const $this = $(this),
                    noteContainer = $('#give-payment-note'),
                    noteTypeContainer = $('#donation_note_type'),
                    postData = {
                        action: 'give_insert_payment_note',
                        payment_id: $(this).data('payment-id'),
                        note: noteContainer.val(),
                        type: noteTypeContainer.val(),
                        _wpnonce: Give.fn.getGlobalVar('give_insert_payment_note_nonce'),
                    };

                if (postData.note) {
                    if ('donor' === postData.type && give_vars.email_notification.donor_note.status) {
                        // Confirm and save note.
                        new Give.modal.GiveConfirmModal({
                            successConfirm: function () {
                                save_note();
                            },
                            modalContent: {
                                desc: give_vars.donor_note_confirm_msg,
                            },
                        }).render();
                    } else {
                        save_note();
                    }
                } else {
                    const border_color = noteContainer.css('border-color');
                    noteContainer.css('border-color', 'red');

                    setTimeout(function () {
                        noteContainer.css('border-color', border_color);
                    }, 500);
                }
            });
        },

        remove_note: function () {
            $('body').on('click', '.give-delete-payment-note', function (e) {
                e.preventDefault();

                const that = this;

                new GiveConfirmModal({
                    modalContent: {
                        title: Give.fn.getGlobalVar('confirm_deletion'),
                        desc: Give.fn.getGlobalVar('delete_payment_note'),
                    },
                    successConfirm: function (args) {
                        const postData = {
                            action: 'give_delete_payment_note',
                            payment_id: $(that).data('payment-id'),
                            note_id: $(that).data('note-id'),
                            _wpnonce: Give.fn.getGlobalVar('give_delete_payment_note_nonce'),
                        };

                        $.ajax({
                            type: 'POST',
                            data: postData,
                            url: ajaxurl,
                            success: function (response) {
                                $('#give-payment-note-' + postData.note_id).remove();
                                if (!$('.give-payment-note').length) {
                                    $('.give-no-payment-notes').show();
                                }
                                return false;
                            },
                        }).fail(function (data) {
                            if (window.console && window.console.log) {
                                console.log(data);
                            }
                        });
                    },
                }).render();
            });
        },

        new_donor: function () {
            $('#give-donor-details').on(
                'click',
                '.give-payment-new-donor, .give-payment-new-donor-cancel',
                function (e) {
                    e.preventDefault();
                    $('.donor-info').toggle();
                    $('.new-donor').toggle();

                    if ($('.new-donor').is(':visible')) {
                        $('#give-new-donor').val(1);
                    } else {
                        $('#give-new-donor').val(0);
                    }
                }
            );
        },

        resend_receipt: function () {
            $('body').on('click', '#give-resend-receipt', function (e) {
                const that = this;

                e.preventDefault();

                new GiveConfirmModal({
                    modalContent: {
                        title: Give.fn.getGlobalVar('confirm_action'),
                        desc: Give.fn.getGlobalVar('resend_receipt'),
                    },
                    successConfirm: function () {
                        window.location.assign($(that).attr('href'));
                    },
                }).render();
            });
        },

        variable_price_list: function () {
            // Update variable price list when form changes.
            $('#give_payment_form_select')
                .chosen()
                .change(function () {
                    let give_form_id,
                        variable_prices_html_container = $('.give-donation-level');

                    // Check for form ID.
                    if (!(give_form_id = $(this).val())) {
                        return false;
                    }

                    // Bailout.
                    if (!variable_prices_html_container.length) {
                        return false;
                    }

                    // Ajax.
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            form_id: give_form_id,
                            payment_id: $('input[name="give_payment_id"]').val(),
                            action: 'give_check_for_form_price_variations_html',
                        },
                        success: function (response) {
                            response = response.trim();
                            if (response) {
                                // Update Variable price html.
                                variable_prices_html_container.html(response);

                                // Add chosen feature to select tag.
                                $('select[name="give-variable-price"]').chosen().change();
                            } else {
                                // Update Variable price html.
                                variable_prices_html_container.html('');
                            }
                        },
                    });
                });

            // Add total donation amount if level changes.
            $('#give-donation-overview').on('change', 'select[name="give-variable-price"]', function () {
                const prices = jQuery(this).data('prices'),
                    $total_amount = $('#give-payment-total');

                if ('' !== prices && $(this).val() in prices) {
                    $total_amount.val(prices[$(this).val()]).css('background-color', 'yellow');

                    window.setTimeout(function () {
                        $total_amount.css('background-color', 'white');
                    }, 1000);
                }
            });
        },
    };

    /**
     * Settings screen JS
     */
    const Give_Settings = {
        init: function () {
            this.toggle_gateways();
            this.setting_change_country();
            this.toggle_options();
            this.main_setting_update_notice();
            this.verify_settings();
            this.saveButtonTriggered();
            this.changeAlert();
            this.detectSettingsChange();
            this.sequentialDonationIDPreview();
        },

        /**
         * Disables the default gateway radio button if the
         * gateway is disabled.
         */
        toggle_gateways: function () {
            const checkbox = $('.gateways-checkbox');

            checkbox.on('click', function () {
                // Get the radio button object related to this checkbox.
                const radio = $(this).prev('.gateways-radio');

                // Get the checked value of the current checbox.
                const checked = this.checked;

                // Get all the checkbox that are checked.
                const checked_cbs = $('.gateways-checkbox:checked');

                // Get the count of all the checked checkbox.
                const count_cbs = checked_cbs.length;

                /**
                 * If there is only one checked checkbox, then
                 * make that gateway the default gateway.
                 */
                if (1 === count_cbs) {
                    checked_cbs.prev('.gateways-radio').attr('checked', 'checked');

                    if (this.checked) {
                        radio.removeAttr('disabled');
                    } else {
                        radio.attr('disabled', 'disabled');
                    }
                } else if (this.checked) {
                    radio.removeAttr('disabled');
                    radio.removeAttr('checked');
                } else {
                    radio.attr('disabled', 'disabled');
                }
            });
        },

        /**
         * Fire when user change the country from the dropdown
         *
         * @since 1.8.14
         */
        setting_change_country: function () {
            $('select[name="base_country"]').change(function () {
                const $this = $(this);
                const data = {
                    action: 'give_get_states',
                    country: $this.val(),
                    field_name: 'base_state',
                };

                $.post(ajaxurl, data, function (response) {
                    // Show the states dropdown menu.
                    $this.closest('tr').next().show();
                    $('#base_state_chosen').remove();
                    if (typeof response.states_found !== undefined && true == response.states_found) {
                        $(':input[name="base_state"]').replaceWith(response.data).addClass('give-select-chosen');
                        $(':input[name="base_state"]').chosen();
                    } else {
                        if (typeof response.show_field !== undefined && false == response.show_field) {
                            // Hide the states dropdown menu.
                            $this.closest('tr').next().hide();
                        }
                        $(':input[name="base_state"]').replaceWith(
                            '<input type="text" name="' +
                                data.field_name +
                                '" value="' +
                                response.default_state +
                                '" class="give-edit-toggles medium-text"/>'
                        );
                    }
                });
                return false;
            });
        },

        toggle_options: function () {
            /**
             * Email access
             */
            const emailAccess = $('input[name="email_access"]', '.give-setting-tab-body-general');
            emailAccess
                .on('change', function () {
                    const fieldValueEmail = $(
                        'input[name="email_access"]:checked',
                        '.give-setting-tab-body-general'
                    ).val();
                    const fieldValueRecaptcha = $(
                        'input[name="enable_recaptcha"]:checked',
                        '.give-setting-tab-body-general'
                    ).val();
                    if ('enabled' === fieldValueEmail) {
                        $('input[name="enable_recaptcha"]').parents('tr').show();

                        if ('enabled' === fieldValueRecaptcha) {
                            $('#recaptcha_key').parents('tr').show();
                            $('#recaptcha_secret').parents('tr').show();
                        } else {
                            $('#recaptcha_key').parents('tr').hide();
                            $('#recaptcha_secret').parents('tr').hide();
                        }
                    } else {
                        $('#recaptcha_key').parents('tr').hide();
                        $('#recaptcha_secret').parents('tr').hide();
                        $('input[name="enable_recaptcha"]').parents('tr').hide();
                    }
                })
                .change();

            /**
             * Email reCAPTCHA
             */
            const recaptcha = $('input[name="enable_recaptcha"]', '.give-setting-tab-body-general');
            recaptcha
                .on('change', function () {
                    const fieldValueEmail = $(
                        'input[name="email_access"]:checked',
                        '.give-setting-tab-body-general'
                    ).val();
                    const fieldValueRecaptcha = $(
                        'input[name="enable_recaptcha"]:checked',
                        '.give-setting-tab-body-general'
                    ).val();

                    if ('enabled' === fieldValueEmail && 'enabled' === fieldValueRecaptcha) {
                        $('#recaptcha_key').parents('tr').show();
                        $('#recaptcha_secret').parents('tr').show();
                    } else {
                        $('#recaptcha_key').parents('tr').hide();
                        $('#recaptcha_secret').parents('tr').hide();
                    }
                })
                .change();

            /**
             * Form featured image
             */
            const form_featured_image = $('input[name="form_featured_img"]', '.give-setting-tab-body-display');
            form_featured_image
                .on('change', function () {
                    const field_value = $(
                        'input[name="form_featured_img"]:checked',
                        '.give-setting-tab-body-display'
                    ).val();
                    if ('enabled' === field_value) {
                        $('#featured_image_size').parents('tr').show();
                    } else {
                        $('#featured_image_size').parents('tr').hide();
                    }
                })
                .change();

            /**
             * Disable admin notification
             */
            const admin_notification = $('input[name="admin_notices"]', '.give-setting-tab-body-emails');
            admin_notification
                .on('change', function () {
                    const field_value = $('input[name="admin_notices"]:checked', '.give-setting-tab-body-emails').val();
                    if ('enabled' === field_value) {
                        $('#donation_notification_subject').parents('tr').show();
                        $('#wp-donation_notification-wrap').parents('tr').show();
                        $('#admin_notice_emails').parents('tr').show();
                    } else {
                        $('#donation_notification_subject').parents('tr').hide();
                        $('#wp-donation_notification-wrap').parents('tr').hide();
                        $('#admin_notice_emails').parents('tr').hide();
                    }
                })
                .change();

            /**
             * Toggle sequential ordering settings
             */
            const sequential_ordering = $('input[name="sequential-ordering_status"]', '.give-setting-tab-body-general');
            sequential_ordering
                .on('change', function () {
                    const field_value = $(
                            'input[name="sequential-ordering_status"]:checked',
                            '.give-setting-tab-body-general'
                        ).val(),
                        $parent = $(this).closest('table');
                    if ('enabled' === field_value) {
                        $('input', $parent).not('input[name="sequential-ordering_status"]').parents('tr').show();
                    } else {
                        $('input', $parent).not('input[name="sequential-ordering_status"]').parents('tr').hide();
                    }
                })
                .change();
        },

        main_setting_update_notice: function () {
            const $setting_message = $('#setting-error-give-setting-updated');
            if ($setting_message.length) {
                // auto hide setting message in 5 seconds.
                window.setTimeout(function () {
                    $setting_message.slideUp();
                }, 5000);
            }
        },

        verify_settings: function () {
            const success_setting = $('#success_page');
            const failure_setting = $('#failure_page');

            /**
             * Verify success and failure page.
             */
            success_setting
                .add(failure_setting)
                .change(function () {
                    if (success_setting.val() === failure_setting.val()) {
                        let notice_html =
                                '<div id="setting-error-give-matched-success-failure-page" class="updated settings-error notice is-dismissible"> <p><strong>' +
                                Give.fn.getGlobalVar('matched_success_failure_page') +
                                '</strong></p> <button type="button" class="notice-dismiss"><span class="screen-reader-text">' +
                                Give.fn.getGlobalVar('dismiss_notice_text') +
                                '</span></button> </div>',
                            $notice_container = $('#setting-error-give-matched-success-failure-page');

                        // Unset setting field.
                        $(this).val('');

                        // Bailout.
                        if ($notice_container.length) {
                            return false;
                        }

                        // Add html.
                        $('h1', '#give-mainform').after(notice_html);
                        $notice_container = $('#setting-error-give-matched-success-failure-page');

                        // Add event to  dismiss button.
                        $('.notice-dismiss', $notice_container).click(function () {
                            $notice_container.remove();
                        });
                    }
                })
                .change();
        },

        saveButtonTriggered: function () {
            $('.give-settings-setting-page').on('click', '.give-save-button', function () {
                $(window).unbind('beforeunload');
            });
        },

        /**
         * Show alert when admin try to reload the page with saving the changes.
         *
         * @since 1.8.14
         */
        changeAlert: function () {
            $(window).bind('beforeunload', function (e) {
                const confirmationMessage = Give.fn.getGlobalVar('setting_not_save_message');

                if (give_setting_edit) {
                    (e || window.event).returnValue = confirmationMessage; //Gecko + IE.
                    return confirmationMessage; //Webkit, Safari, Chrome.
                }
            });
        },

        /**
         * Display alert in setting page of give if user try to reload the page with saving the changes.
         *
         * @since 1.8.14
         */
        detectSettingsChange: function () {
            const settingsPage = $('.give-settings-setting-page');

            // Check if it give setting page or not.
            if (settingsPage.length > 0) {
                // Get the default value.
                const on_load_value = $('#give-mainform').serialize();

                /**
                 * Keyup event add to support to text box and textarea.
                 * blur event add to support to dropdown.
                 * Change event add to support to rest all element.
                 */
                settingsPage.on('change keyup blur', 'form', function (event) {
                    // Do not listen for excluded form fields. They are there own logic to handle their state.
                    if ($(event.target).closest('.js-fields-has-custom-saving-logic').length) {
                        return;
                    }

                    // Get the form value after change.
                    const on_change_value = $('#give-mainform').serialize();

                    // If both the value are same then no change has being made else change has being made.
                    give_setting_edit = on_load_value !== on_change_value ? true : false;
                });
            }
        },

        /**
         * Render donation id for sequential ordering.
         *
         * @since 2.1.0
         */
        sequentialDonationIDPreview: function () {
            const $previewField = jQuery('#sequential-ordering_preview');

            // Bailout.
            if (!$previewField.length) {
                return;
            }

            jQuery(
                '#sequential-ordering_number_prefix, #sequential-ordering_number, #sequential-ordering_number_padding, #sequential-ordering_number_suffix'
            ).on('keyup change', function () {
                const prefix = jQuery('#sequential-ordering_number_prefix').val(),
                    startingNumber = jQuery('#sequential-ordering_number').val().trim() || '1',
                    numberPadding = jQuery('#sequential-ordering_number_padding').val().trim(),
                    suffix = jQuery('#sequential-ordering_number_suffix').val(),
                    $donationID = `${prefix}${startingNumber.padStart(numberPadding, '0')}${suffix}`;

                $previewField.val($donationID);
            });

            jQuery('#sequential-ordering_number_prefix').trigger('keyup');

            jQuery('#sequential-ordering_number_prefix, #sequential-ordering_number_suffix').on('blur', function () {
                $(this).val($(this).val().replace(new RegExp(' ', 'g'), '-'));
            });
        },
    };

    /**
     * Reports / Exports / Tools screen JS
     */
    const Give_Reports = {
        init: function () {
            this.date_options();
            this.donors_export();
            this.recount_stats();
        },

        date_options: function () {
            // Show hide extended date options.
            $('#give-graphs-date-options').change(function () {
                const $this = $(this);
                if ('other' === $this.val()) {
                    $('#give-date-range-options').show();
                } else {
                    $('#give-date-range-options').hide();
                }
            });
        },

        donors_export: function () {
            // Show / hide Donation Form option when exporting donors.
            $('#give_donor_export_form').change(function () {
                const $this = $(this),
                    form_id = $('option:selected', $this).val(),
                    customer_export_option = $('#give_customer_export_option');

                if ('0' === $this.val()) {
                    customer_export_option.show();
                } else {
                    customer_export_option.hide();
                }

                const price_options_select = $('.give_price_options_select');

                // On Form Select, Check if Variable Prices Exist.
                if (parseInt(form_id) != 0) {
                    const data = {
                        action: 'give_check_for_form_price_variations',
                        form_id: form_id,
                        all_prices: true,
                    };

                    $.post(ajaxurl, data, function (response) {
                        price_options_select.remove();
                        $('#give_donor_export_form_chosen').after(response);
                    });
                } else {
                    price_options_select.remove();
                }
            });
        },

        recount_stats: function () {
            $('body').on('change', '#recount-stats-type', function () {
                const export_form = $('#give-tools-recount-form');
                const selected_type = $('option:selected', this).data('type');
                const submit_button = $('#recount-stats-submit');
                const forms = $('.tools-form-dropdown');
                const dateSelector = $('.tools-date-dropdown-delete-donations');

                // Reset the form
                export_form.find('.notice-wrap').remove();
                submit_button.removeClass('button-disabled').attr('disabled', false);
                forms.hide();
                dateSelector.hide();

                $('.give-recount-stats-descriptions span').hide();

                if ('reset-stats' === selected_type) {
                    export_form.append('<div class="notice-wrap"></div>');
                    var notice_wrap = export_form.find('.notice-wrap');
                    notice_wrap.html(
                        '<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' +
                            Give.fn.getGlobalVar('reset_stats_warn') +
                            '</label></p></div>'
                    );
                    submit_button.addClass('button-disabled').attr('disabled', 'disabled');

                    // Add check when admin try to delete all the test donors.
                } else if ('delete-test-donors' === selected_type) {
                    export_form.append('<div class="notice-wrap"></div>');
                    var notice_wrap = export_form.find('.notice-wrap');
                    notice_wrap.html(
                        '<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' +
                            Give.fn.getGlobalVar('delete_test_donor') +
                            '</label></p></div>'
                    );
                    submit_button.addClass('button-disabled').attr('disabled', 'disabled');
                    // Add check when admin try to delete all the imported donations.
                } else if ('delete-import-donors' === selected_type) {
                    export_form.append('<div class="notice-wrap"></div>');
                    var notice_wrap = export_form.find('.notice-wrap');
                    notice_wrap.html(
                        '<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' +
                            Give.fn.getGlobalVar('delete_import_donor') +
                            '</label></p></div>'
                    );
                    submit_button.addClass('button-disabled').attr('disabled', 'disabled');
                } else if ('delete-donations' === selected_type) {
                    dateSelector.show();
                    export_form.append('<div class="notice-wrap"></div>');
                    var notice_wrap = export_form.find('.notice-wrap');
                    notice_wrap.html(
                        '<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' +
                            Give.fn.getGlobalVar('delete_donations_only') +
                            '</label></p></div>'
                    );
                    submit_button.addClass('button-disabled').attr('disabled', 'disabled');
                } else {
                    forms.hide();
                    forms.val(0);
                }

                const current_forms = $('.tools-form-dropdown-' + selected_type);
                current_forms.show();
                current_forms.find('.give-select-chosen').css({
                    width: 'auto',
                    'min-width': '250px',
                });
                $('#' + selected_type).show();
            });

            $('body').on('change', '#confirm-reset', function () {
                const checked = $(this).is(':checked');
                if (checked) {
                    $('#recount-stats-submit').removeClass('button-disabled').removeAttr('disabled');
                } else {
                    $('#recount-stats-submit').addClass('button-disabled').attr('disabled', 'disabled');
                }
            });

            $('#give-tools-recount-form').submit(function (e) {
                const selection = $('#recount-stats-type').val();
                const export_form = $(this);
                const selected_type = $('option:selected', this).data('type');

                if ('reset-stats' === selected_type) {
                    const is_confirmed = $('#confirm-reset').is(':checked');
                    if (is_confirmed) {
                        return true;
                    }
                    has_errors = true;
                }

                export_form.find('.notice-wrap').remove();

                export_form.append('<div class="notice-wrap"></div>');
                const notice_wrap = export_form.find('.notice-wrap');
                var has_errors = false;

                if (null === selection || 0 === selection) {
                    // Needs to pick a method give_vars.batch_export_no_class.
                    notice_wrap.html(
                        '<div class="updated error"><p>' + Give.fn.getGlobalVar('batch_export_no_class') + '</p></div>'
                    );
                    has_errors = true;
                }

                if ('recount-form' === selected_type) {
                    const selected_form = $('select[name="form_id"]').val();
                    if (selected_form == 0) {
                        // Needs to pick give_vars.batch_export_no_reqs.
                        notice_wrap.html(
                            '<div class="updated error"><p>' +
                                Give.fn.getGlobalVar('batch_export_no_reqs') +
                                '</p></div>'
                        );
                        has_errors = true;
                    }
                }

                if (has_errors) {
                    export_form.find('.button-disabled').removeClass('button-disabled');
                    return false;
                }
            });
        },
    };

    /**
     * Export screen JS
     */
    const Give_Export = {
        init: function () {
            this.submit();
            this.dismiss_message();
        },

        submit: function () {
            const self = this;

            $(document.body).on('submit', '.give-export-form', function (e) {
                e.preventDefault();

                const submitButton = $(this).find('input[type="submit"]');

                if (!submitButton.hasClass('button-disabled')) {
                    const data = $(this).serialize();

                    submitButton.addClass('button-disabled');
                    $('form.give-export-form select').attr('disabled', true).trigger('chosen:updated');

                    let parent_notices = $(this);

                    // show notices inside add-notices class
                    if ($(this).find('.add-notices').length > 0) {
                        parent_notices = $(this).find('.add-notices');
                    }

                    parent_notices.find('.notice-wrap').remove();
                    parent_notices.append(
                        '<div class="notice-wrap give-clearfix"><span class="spinner is-active"></span><div class="give-progress"><div></div></div></div>'
                    );

                    // start the process
                    self.process_step(1, data, self, this);
                }
            });
        },

        process_step: function (step, data, self, form, file) {
            /**
             * Do not allow user to reload the page
             *
             * @since 1.8.14
             */
            give_setting_edit = true;

            let reset_form = false;

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    form: data,
                    action: 'give_do_ajax_export',
                    step: step,
                    file_name: file,
                },
                dataType: 'json',
                success: function (response) {
                    if ('done' == response.step || response.error || response.success) {
                        /**
                         * Now allow user to reload the page
                         *
                         * @since 1.8.14
                         */
                        give_setting_edit = false;

                        reset_form = true;

                        // We need to get the actual in progress form, not all forms on the page
                        const notice_wrap = $(form).parent().find('.notice-wrap');
                        const export_form = notice_wrap.find('.give-progress');

                        $(form).find('.button-disabled').removeClass('button-disabled');
                        $(form).find('select').attr('disabled', false).trigger('chosen:updated');

                        if (response.error) {
                            const error_message = response.message;
                            notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');
                        } else if (response.success) {
                            const success_message = response.message;
                            notice_wrap.html(
                                '<div id="give-batch-success" class="updated notice is-dismissible"><p>' +
                                    success_message +
                                    '<span class="notice-dismiss"></span></p></div>'
                            );
                        } else {
                            notice_wrap.remove();
                            window.location = response.url;
                        }
                    } else {
                        $('.give-progress div').animate(
                            {
                                width: response.percentage + '%',
                            },
                            50,
                            function () {
                                // Animation complete.
                            }
                        );
                        self.process_step(parseInt(response.step), data, self, form, response.file_name);
                    }

                    if (true === reset_form && $('#give-tools-recount-form').length > 0) {
                        // Reset the form for preventing multiple ajax request.
                        $('#give-tools-recount-form')[0].reset();
                        $('#give-tools-recount-form .tools-form-dropdown').hide();
                        $('#give-tools-recount-form .tools-date-dropdown').hide();
                        $('#give-tools-recount-form .tools-form-dropdown-recount-form-select')
                            .val('0')
                            .trigger('chosen:updated');
                    }
                },
            }).fail(function (response) {
                /**
                 * Now allow user to reload the page
                 *
                 * @since 1.8.14
                 */
                give_setting_edit = false;

                if (window.console && window.console.log) {
                    console.log(response);
                }
                $('.notice-wrap').append(response.responseText);
            });
        },

        dismiss_message: function () {
            $('body').on('click', '#give-batch-success .notice-dismiss', function () {
                $('#give-batch-success').parent().slideUp('fast');
            });
        },
    };

    /**
     * Updates screen JS
     */
    var Give_Updates = {
        el: {},

        init: function () {
            this.submit();
            this.dismiss_message();
        },

        submit: function () {
            const $self = this,
                step = 1,
                resume_update_step = 0;

            $self.el.main_container = Give_Selector_Cache.get('#give-db-updates');
            $self.el.update_link = Give_Selector_Cache.get('.give-update-now', $self.el.main_container);
            $self.el.run_upload_container = Give_Selector_Cache.get(
                '.give-run-database-update',
                $self.el.progress_main_container
            );
            $self.el.progress_main_container = Give_Selector_Cache.get('.progress-container', $self.el.main_container);
            $self.el.heading = Give_Selector_Cache.get('.update-message', $self.el.progress_main_container);
            $self.el.progress_container = Give_Selector_Cache.get(
                '.progress-content',
                $self.el.progress_main_container
            );
            $self.el.update_progress_counter = Give_Selector_Cache.get($('.give-update-progress-count'));

            if ($self.el.main_container.data('resume-update')) {
                $self.el.update_link.addClass('active').hide().removeClass('give-hidden');

                if (!$('#give-restart-upgrades').length) {
                    // Start update by ajax if background update does not work.
                    if (!Give.fn.getGlobalVar('ajax').length) {
                        window.setTimeout(Give_Updates.start_db_update, 1000);
                    }

                    window.setTimeout(Give_Updates.get_db_updates_info, 1000, $self);
                }
            }

            // Bailout.
            if ($self.el.update_link.hasClass('active')) {
                return;
            }

            $self.el.update_link.on('click', '', function (e) {
                e.preventDefault();

                $self.el.run_upload_container.find('.notice').remove();
                $self.el.run_upload_container.append(
                    '<div class="notice notice-error non-dismissible give-run-update-containt"><p> <a href="#" class="give-run-update-button button">' +
                        Give.fn.getGlobalVar('db_update_confirmation_msg_button') +
                        '</a> ' +
                        Give.fn.getGlobalVar('db_update_confirmation_msg') +
                        '</p></div>'
                );
            });

            $('#give-db-updates').on('click', 'a.give-run-update-button', function (e) {
                e.preventDefault();

                if ($(this).hasClass('active')) {
                    return false;
                }

                $(this).addClass('active').fadeOut();
                $self.el.update_link.addClass('active').fadeOut();
                $('#give-db-updates .give-run-update-containt').slideUp();

                $self.el.progress_container.find('.notice-wrap').remove();
                $self.el.progress_container.append(
                    '<div class="notice-wrap give-clearfix"><span class="spinner is-active"></span><div class="give-progress"><div></div></div></div>'
                );
                $self.el.progress_main_container.removeClass('give-hidden');

                Give_Updates.start_db_update();

                window.setTimeout(Give_Updates.get_db_updates_info, 500, $self);

                return false;
            });
        },

        start_db_update: function start_db_update() {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'give_run_db_updates',
                    run_db_update: 1,
                    nonce: Give.fn.getGlobalVar('db_update_nonce'),
                },
                dataType: 'json',
                success: function success(response) {},
            }).always(function () {
                // Start update by ajax if background update does not work.
                if (!Give.fn.getGlobalVar('ajax').length) {
                    window.setTimeout(Give_Updates.start_db_update, 1000);
                }
            });
        },

        get_db_updates_info: function ($self) {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'give_db_updates_info',
                },
                dataType: 'json',
                success: function (response) {
                    // We need to get the actual in progress form, not all forms on the page.
                    const notice_wrap = Give_Selector_Cache.get('.notice-wrap', $self.el.progress_container, true);

                    if (-1 !== $.inArray('success', Object.keys(response))) {
                        if (response.success) {
                            if ($self.el.update_progress_counter.length) {
                                $self.el.update_progress_counter.text('100%');
                            }

                            // Update steps info.
                            if (-1 !== $.inArray('heading', Object.keys(response.data))) {
                                $self.el.heading.html('<strong>' + response.data.heading + '</strong>');
                            }

                            $self.el.update_link.closest('p').remove();
                            notice_wrap.html(
                                '<div class="notice notice-success is-dismissible"><p>' +
                                    response.data.message +
                                    '</p><button type="button" class="notice-dismiss"></button></div>'
                            );
                        } else {
                            // Update steps info.
                            if (-1 !== $.inArray('heading', Object.keys(response.data))) {
                                $self.el.heading.html('<strong>' + response.data.heading + '</strong>');
                            }

                            if (response.data.message) {
                                $self.el.update_link.closest('p').remove();
                                notice_wrap.html(
                                    '<div class="notice notice-error is-dismissible"><p>' +
                                        response.data.message +
                                        '</p><button type="button" class="notice-dismiss"></button></div>'
                                );
                            } else {
                                setTimeout(function () {
                                    $self.el.update_link.removeClass('active').show();
                                    $self.el.progress_main_container.addClass('give-hidden');
                                }, 1000);
                            }
                        }
                    } else if (response && -1 !== $.inArray('percentage', Object.keys(response.data))) {
                        if ($self.el.update_progress_counter.length) {
                            $self.el.update_progress_counter.text(response.data.total_percentage + '%');
                        }

                        // Update steps info.
                        if (-1 !== $.inArray('heading', Object.keys(response.data))) {
                            $self.el.heading.html('<strong>' + response.data.heading + '</strong>');
                        }

                        // Update progress.
                        $('.give-progress div', '#give-db-updates').animate(
                            {
                                width: response.data.percentage + '%',
                            },
                            50,
                            function () {
                                // Animation complete.
                            }
                        );

                        window.setTimeout(Give_Updates.get_db_updates_info, 1000, $self);
                    } else {
                        notice_wrap.html(
                            '<div class="notice notice-error"><p>' +
                                Give.fn.getGlobal().updates.ajax_error +
                                '</p></div>'
                        );

                        setTimeout(function () {
                            $self.el.update_link.removeClass('active').show();
                            $self.el.progress_main_container.addClass('give-hidden');
                        }, 1000);
                    }
                },
            });
        },

        process_step: function (step, update, $self) {
            give_setting_edit = true;

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'give_do_ajax_updates',
                    step: parseInt(step),
                    update: parseInt(update),
                },
                dataType: 'json',
                success: function (response) {
                    give_setting_edit = false;

                    // We need to get the actual in progress form, not all forms on the page.
                    const notice_wrap = Give_Selector_Cache.get('.notice-wrap', $self.el.progress_container, true);

                    if (-1 !== $.inArray('success', Object.keys(response))) {
                        if (response.success) {
                            // Update steps info.
                            if (-1 !== $.inArray('heading', Object.keys(response.data))) {
                                $self.el.heading.html('<strong>' + response.data.heading + '</strong>');
                            }

                            $self.el.update_link.closest('p').remove();
                            notice_wrap.html(
                                '<div class="notice notice-success is-dismissible"><p>' +
                                    response.data.message +
                                    '</p><button type="button" class="notice-dismiss"></button></div>'
                            );
                        } else {
                            // Update steps info.
                            if (-1 !== $.inArray('heading', Object.keys(response.data))) {
                                $self.el.heading.html('<strong>' + response.data.heading + '</strong>');
                            }

                            notice_wrap.html(
                                '<div class="notice notice-error"><p>' + response.data.message + '</p></div>'
                            );

                            setTimeout(function () {
                                $self.el.update_link.removeClass('active').show();
                                $self.el.progress_main_container.addClass('give-hidden');
                            }, 5000);
                        }
                    } else if (response && -1 !== $.inArray('percentage', Object.keys(response.data))) {
                        // Update progress.
                        $('.give-progress div', '#give-db-updates').animate(
                            {
                                width: response.data.percentage + '%',
                            },
                            50,
                            function () {
                                // Animation complete.
                            }
                        );

                        // Update steps info.
                        if (-1 !== $.inArray('heading', Object.keys(response.data))) {
                            $self.el.heading.html(
                                '<strong>' +
                                    response.data.heading.replace(
                                        '{update_count}',
                                        $self.el.heading.data('update-count')
                                    ) +
                                    '</strong>'
                            );
                        }

                        $self.process_step(parseInt(response.data.step), response.data.update, $self);
                    } else {
                        notice_wrap.html(
                            '<div class="notice notice-error"><p>' +
                                Give.fn.getGlobal().updates.ajax_error +
                                '</p></div>'
                        );

                        setTimeout(function () {
                            $self.el.update_link.removeClass('active').show();
                            $self.el.progress_main_container.addClass('give-hidden');
                        }, 5000);
                    }
                },
            })
                .fail(function (response) {
                    give_setting_edit = false;

                    if (window.console && window.console.log) {
                        console.log(response);
                    }

                    Give_Selector_Cache.get('.notice-wrap', self.el.progress_container).append(response.responseText);
                })
                .always(function () {});
        },

        dismiss_message: function () {
            $('body').on('click', '#poststuff .notice-dismiss', function () {
                $(this).parent().slideUp('fast');
            });
        },
    };

    /**
     * Give Upgrader
     */
    const Give_Upgrades = {
        init: function () {
            this.restartUpgrade();
            this.stopUpgrade();
            this.restartUpdater();
        },

        /**
         * Function to restart the upgrade process.
         */
        restartUpgrade: function () {
            jQuery('#give-restart-upgrades').click('click', function (e) {
                const that = this;

                e.preventDefault();

                jQuery('.give-doing-update-text-p').show();
                jQuery('.give-update-paused-text-p').hide();

                new GiveConfirmModal({
                    modalContent: {
                        title: Give.fn.getGlobalVar('confirm_action'),
                        desc: Give.fn.getGlobalVar('restart_upgrade'),
                    },
                    successConfirm: function () {
                        window.location.assign(jQuery(that).data('redirect-url'));
                    },
                }).render();
            });
        },

        /**
         * Function to pause the upgrade process.
         */
        stopUpgrade: function () {
            jQuery('#give-pause-upgrades').click('click', function (e) {
                const that = this;

                e.preventDefault();

                jQuery('.give-doing-update-text-p').hide();
                jQuery('.give-update-paused-text-p').show();

                new GiveConfirmModal({
                    modalContent: {
                        title: Give.fn.getGlobalVar('confirm_action'),
                        desc: Give.fn.getGlobalVar('stop_upgrade'),
                    },
                    successConfirm: function () {
                        window.location.assign(jQuery(that).data('redirect-url'));
                    },
                }).render();
            });
        },

        /**
         * Function to restart the update process.
         */
        restartUpdater: function () {
            jQuery('.give-restart-updater-btn,.give-run-update-now').click('click', function (e) {
                const that = this;

                e.preventDefault();

                new GiveConfirmModal({
                    modalContent: {
                        title: Give.fn.getGlobalVar('confirm_action'),
                        desc: Give.fn.getGlobalVar('restart_update'),
                    },
                    successConfirm: function () {
                        window.location.assign(jQuery(that).attr('href'));
                    },
                }).render();
            });
        },
    };

    /**
     * Admin Status Select Field Change
     *
     * @description: Handle status switching
     * @since: 1.0
     */
    const handle_status_change = function () {
        $('select[name="give-payment-status"]').on('change', function () {
            const status = $(this).val();

            $('.give-donation-status')
                .removeClass(function (index, css) {
                    return (css.match(/\bstatus-\S+/g) || []).join(' ');
                })
                .addClass('status-' + status);
        });
    };

    /**
     * Donor management screen JS
     */
    var GiveDonor = {
        onLoadPageNumber: '',

        init: function () {
            this.loadGravatar();
            this.unlockDonorFields();
            this.editDonor();
            this.add_email();
            this.removeUser();
            this.cancelEdit();
            this.add_note();
            this.delete_checked();
            this.addressesAction();
            this.bulkDeleteDonor();
            GiveDonor.onLoadPageNumber = $('#current-page-selector').val();
            $('body').on('click', '#give-donors-filter .bulkactions input[type="submit"]', this.handleBulkActions);
        },

        loadGravatar: function () {
            let giveDonorImage,
                donorEmail,
                hasValidGravatar = '';

            $('.give-donor-name').each(function () {
                giveDonorImage = $(this).find('.give-donor__image');

                // Bailout out if already tried to load gravatar.
                if (giveDonorImage.hasClass('gravatar-loaded')) {
                    return;
                }

                donorEmail = giveDonorImage.attr('data-donor_email');
                hasValidGravatar = '1' === giveDonorImage.attr('data-has-valid-gravatar');

                if (hasValidGravatar) {
                    // executes when complete page is fully loaded, including all frames, objects and images
                    const donorImage = $('<img>');
                    donorImage.attr('src', gravatar.url(donorEmail));
                    donorImage.attr('width', '60');
                    donorImage.attr('height', '60');

                    $(this).find('.give-donor__image').html(donorImage);
                }

                giveDonorImage.addClass('gravatar-loaded');
            });
        },

        unlockDonorFields: function (e) {
            $('body').on('click', '.give-lock-block', function (e) {
                new GiveErrorAlert({
                    modalContent: {
                        title: Give.fn.getGlobalVar('unlock_donor_fields_title'),
                        desc: Give.fn.getGlobalVar('unlock_donor_fields_message'),
                        cancelBtnTitle: Give.fn.getGlobalVar('ok'),
                    },
                }).render();
                e.preventDefault();
            });
        },

        editDonor: function () {
            $('body').on('click', '#edit-donor', function (e) {
                e.preventDefault();
                $('#give-donor-card-wrapper .editable').hide();
                $('#give-donor-card-wrapper .edit-item').fadeIn().css('display', 'block');
            });
        },

        removeUser: function () {
            $('body').on('click', '#disconnect-donor', function (e) {
                e.preventDefault();

                new GiveConfirmModal({
                    modalWrapper: 'give-modal--warning',
                    modalContent: {
                        desc: Give.fn.getGlobalVar('disconnect_user'),
                    },
                    successConfirm: function () {
                        const donorID = $('input[name="donor_info[id]"]').val();

                        const postData = {
                            give_action: 'disconnect-userid',
                            customer_id: donorID,
                            _wpnonce: $('#edit-donor-info #_wpnonce').val(),
                        };

                        $.post(
                            ajaxurl,
                            postData,
                            function (response) {
                                window.location.href = response.redirect;
                            },
                            'json'
                        );
                    },
                }).render();

                return false;
            });
        },

        cancelEdit: function () {
            $('body').on('click', '#give-edit-donor-cancel', function (e) {
                e.preventDefault();
                $('#give-donor-card-wrapper .edit-item').hide();
                $('#give-donor-card-wrapper .editable').show();
                $('.give_user_search_results').html('');
            });
        },

        add_note: function () {
            $('body').on('click', '#add-donor-note', function (e) {
                e.preventDefault();
                const postData = {
                    give_action: 'add-donor-note',
                    customer_id: $('#donor-id').val(),
                    donor_note: $('#donor-note').val(),
                    add_donor_note_nonce: $('#add_donor_note_nonce').val(),
                };

                if (postData.donor_note) {
                    $.ajax({
                        type: 'POST',
                        data: postData,
                        url: ajaxurl,
                        success: function (response) {
                            $('#give-donor-notes').prepend(response);
                            $('.give-no-donor-notes').hide();
                            $('#donor-note').val('');
                        },
                    }).fail(function (data) {
                        if (window.console && window.console.log) {
                            console.log(data);
                        }
                    });
                } else {
                    const border_color = $('#donor-note').css('border-color');
                    $('#donor-note').css('border-color', 'red');
                    setTimeout(function () {
                        $('#donor-note').css('border-color', border_color);
                    }, 500);
                }
            });
        },
        delete_checked: function () {
            $('#give-donor-delete-confirm').change(function () {
                const records_input = $('#give-donor-delete-records');
                const submit_button = $('#give-delete-donor');

                if ($(this).prop('checked')) {
                    records_input.attr('disabled', false);
                    submit_button.attr('disabled', false);
                } else {
                    records_input.attr('disabled', true);
                    records_input.prop('checked', false);
                    submit_button.attr('disabled', true);
                }
            });
        },
        add_email: function () {
            if (!$('#add-donor-email').length) {
                return;
            }

            $(document.body).on('click', '#add-donor-email', function (e) {
                e.preventDefault();
                const button = $(this);
                const wrapper = button.parent();

                wrapper.parent().find('.notice-wrap').remove();
                wrapper.find('.spinner').css('visibility', 'visible');
                button.attr('disabled', true);

                const customer_id = wrapper.find('input[name="donor-id"]').val();
                const email = wrapper.find('input[name="additional-email"]').val();
                const primary = wrapper.find('input[name="make-additional-primary"]').is(':checked');
                const nonce = wrapper.find('input[name="add_email_nonce"]').val();

                const postData = {
                    give_action: 'add_donor_email',
                    customer_id: customer_id,
                    email: email,
                    primary: primary,
                    _wpnonce: nonce,
                };

                $.post(
                    ajaxurl,
                    postData,
                    function (response) {
                        if (true === response.success) {
                            window.location.href = response.redirect;
                        } else {
                            button.attr('disabled', false);
                            wrapper.after(
                                '<div class="notice-wrap"><div class="notice notice-error inline"><p>' +
                                    response.message +
                                    '</p></div></div>'
                            );
                            wrapper.find('.spinner').css('visibility', 'hidden');
                        }
                    },
                    'json'
                );
            });
        },

        addressesAction: function () {
            const $obj = this,
                $addressWrapper = $('#donor-address-wrapper'),
                $allAddress = $('.all-address', $addressWrapper),
                $noAddressMessageWrapper = $('.give-no-address-message', $addressWrapper),
                $allAddressParent = $($allAddress).parent(),
                $addressForm = $('.address-form', $addressWrapper),
                $addressFormCancelBtn = $('.js-cancel', $addressForm),
                $addressFormCountryField = $('select[name="country"]', $addressForm),
                $addNewAddressBtn = $('.add-new-address', $addressWrapper),
                donorID = parseInt($('input[name="donor-id"]').val());

            $addressFormCountryField.on('change', function () {
                $(this).trigger('chosen:updated');
            });

            // Edit current address button event.
            $allAddress.on('click', '.js-edit', function (e) {
                const $parent = $(this).closest('.address');

                e.preventDefault();

                // Remove notice.
                $('.notice', $allAddressParent).remove();

                $obj.__set_address_form_val($parent);
                $obj.__set_address_form_action('update', $parent.data('address-id'));

                $addNewAddressBtn.hide();
                $allAddress.addClass('give-hidden');
                $addressForm.removeClass('add-new-address-form-hidden');
                $addressForm.data('process', 'update');
            });

            // Remove address button event.
            $allAddress.on('click', '.js-remove', function (e) {
                e.preventDefault();

                const $parent = $(this).closest('.address');

                // Remove notice.
                $('.notice', $allAddressParent).remove();

                $addressForm.data('changed', true);
                $obj.__set_address_form_val($parent);
                $obj.__set_address_form_action('remove', $parent.data('address-id'));

                $addressForm.trigger('submit');
            });

            // Add new address button event.
            $addNewAddressBtn.on('click', function (e) {
                e.preventDefault();

                // Remove notice.
                $('.notice', $allAddressParent).remove();

                $(this).hide();
                $allAddress.addClass('give-hidden');
                $addressForm.removeClass('add-new-address-form-hidden');
                $obj.__set_address_form_action('add');

                $obj.__set_address_form_action();
            });

            // Cancel add new address form button event.
            $addressFormCancelBtn.on('click', function (e) {
                e.preventDefault();

                // Reset form.
                $addressForm.find('input[type="text"]').val('');

                $addNewAddressBtn.show();
                $allAddress.removeClass('give-hidden');
                $addressForm.addClass('add-new-address-form-hidden');
            });

            // Save address.
            $addressForm
                .on('change', function () {
                    $(this).data('changed', true);
                })
                .on('submit', function (e) {
                    e.preventDefault();

                    const $this = $(this);

                    // Remove notice.
                    $('.notice', $allAddressParent).remove();

                    // Do not send ajax if form does not change.
                    if (!$(this).data('changed')) {
                        $addNewAddressBtn.show();
                        $allAddress.removeClass('give-hidden');
                        $addressForm.addClass('add-new-address-form-hidden');

                        return false;
                    }

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'donor_manage_addresses',
                            donorID: donorID,
                            form: $('form', $addressForm).serialize(),
                        },
                        beforeSend: function () {
                            giveAjaxLoader($addressWrapper, {show: true});
                        },
                        success: function (response) {
                            giveAjaxLoader($addressWrapper);

                            if (response.success) {
                                let parent;

                                switch (response.data.action) {
                                    case 'add':
                                        $('.give-grid-row', $allAddress).append(response.data.address_html);

                                        if (
                                            !$noAddressMessageWrapper.hasClass('give-hidden') &&
                                            $('div.give-grid-col-4', $allAddress).length
                                        ) {
                                            $noAddressMessageWrapper.addClass('give-hidden');
                                        }
                                        break;

                                    case 'remove':
                                        parent = $allAddress
                                            .find('div[data-address-id*="' + response.data.id + '"]')
                                            .parent();

                                        if (parent.length) {
                                            parent.animate({'margin-left': '-999'}, 1000, function () {
                                                parent.remove();

                                                if (
                                                    $noAddressMessageWrapper.hasClass('give-hidden') &&
                                                    !$('div.give-grid-col-4', $allAddress).length
                                                ) {
                                                    $noAddressMessageWrapper.removeClass('give-hidden');
                                                }
                                            });
                                        }

                                        break;

                                    case 'update':
                                        parent = $allAddress
                                            .find('div[data-address-id*="' + response.data.id + '"]')
                                            .parent();
                                        var $prevParent = parent.prev(),
                                            $nextParent = {},
                                            is_address_added = false;

                                        if (parseInt($('.give-grid-row>div', $allAddress).length) < 2) {
                                            $('.give-grid-row', $allAddress).append(response.data.address_html);
                                        } else {
                                            if ($prevParent.length) {
                                                $prevParent.after(response.data.address_html);
                                                is_address_added = true;
                                            }

                                            if (!is_address_added) {
                                                $nextParent = parent.next();

                                                if ($nextParent.length) {
                                                    $nextParent.before(response.data.address_html);
                                                }
                                            }
                                        }

                                        parent.remove();

                                        break;
                                }

                                $allAddressParent.prepend(response.data.success_msg);
                            } else {
                                $allAddressParent.prepend(response.data.error_msg);
                            }
                        },
                        dataType: 'json',
                    }).always(function () {
                        $this.data('changed', false);

                        // Reset form.
                        $addressForm.find('input[type="text"]').val('');

                        $addNewAddressBtn.show();
                        $allAddress.removeClass('give-hidden');
                        $addressForm.addClass('add-new-address-form-hidden');
                    });

                    return false;
                });
        },

        __set_address_form_action: function (addressAction, addressID) {
            const $addressWrapper = $('#donor-address-wrapper'),
                $addressForm = $('.address-form', $addressWrapper),
                $addressActionField = $('input[name="address-action"]', $addressForm),
                $addressIDField = $('input[name="address-id"]', $addressForm);

            addressAction = addressAction || 'add';
            addressID = addressID || 'billing';

            $addressActionField.val(addressAction);
            $addressIDField.val(addressID);
        },

        __set_address_form_val: function ($form) {
            const $addressWrapper = $('#donor-address-wrapper'),
                $addressForm = $('.address-form', $addressWrapper),
                state = $('[data-address-type="state"]', $form).text().substr(2).trim(); // State will be like ", HR".

            if (
                $('select[name="country"]', $addressForm).val().trim() !==
                $('[data-address-type="country"]', $form).text().trim()
            ) {
                $('select[name="country"]', $addressForm)
                    .val($('[data-address-type="country"]', $form).text().trim())
                    .trigger('chosen:updated')
                    .change();

                // Update state after some time because state load by ajax for each country.
                window.setTimeout(function () {
                    $('[name="state"]', $addressForm).val(state).trigger('chosen:updated');
                }, 500);
            } else {
                $('[name="state"]', $addressForm).val(state).trigger('chosen:updated');
            }

            $('input[name="line1"]', $addressForm).val($('[data-address-type="line1"]', $form).text().trim());
            $('input[name="line2"]', $addressForm).val($('[data-address-type="line2"]', $form).text().trim());
            $('input[name="city"]', $addressForm).val($('[data-address-type="city"]', $form).text().trim());
            $('input[name="zip"]', $addressForm).val($('[data-address-type="zip"]', $form).text().trim());
        },

        bulkDeleteDonor: function () {
            const $body = $('body');

            // Cancel button click event for donor.
            $body.on('click', '#give-bulk-delete-cancel', function (e) {
                $(this).closest('tr').hide();
                $('.give-skip-donor').trigger('click');
                e.preventDefault();
            });

            // Select All checkbox.
            $body.on('click', '#cb-select-all-1, #cb-select-all-2', function () {
                const selectAll = $(this);

                // Loop through donor selector checkbox.
                $.each($('.donor-selector'), function () {
                    const donorId = $(this).val(),
                        donorName = $(this).data('name'),
                        donorHtml =
                            '<div id="give-donor-' +
                            donorId +
                            '" data-id="' +
                            donorId +
                            '">' +
                            '<a class="give-skip-donor" title="' +
                            Give.fn.getGlobalVar('remove_from_bulk_delete') +
                            '">X</a>' +
                            donorName +
                            '</div>';

                    if (selectAll.is(':checked') && !$(this).is(':checked')) {
                        $('#give-bulk-donors').append(donorHtml);
                    } else if (!selectAll.is(':checked')) {
                        $('#give-bulk-donors')
                            .find('#give-donor-' + donorId)
                            .remove();
                    }
                });
            });

            // On checking checkbox, add to bulk delete donor.
            $body.on('click', '.donor-selector', function () {
                const donorId = $(this).val(),
                    donorName = $(this).data('name'),
                    donorHtml =
                        '<div id="give-donor-' +
                        donorId +
                        '" data-id="' +
                        donorId +
                        '">' +
                        '<a class="give-skip-donor" title="' +
                        Give.fn.getGlobalVar('remove_from_bulk_delete') +
                        '">X</a>' +
                        donorName +
                        '</div>';

                if ($(this).is(':checked')) {
                    $('#give-bulk-donors').prepend(donorHtml);
                } else {
                    $('#give-bulk-donors')
                        .find('#give-donor-' + donorId)
                        .remove();
                }
            });

            // CheckBox click event to confirm deletion of donor.
            $body.on('click', '#give-bulk-delete .give-donor-delete-confirm', function () {
                if ($(this).is(':checked')) {
                    $('#give-bulk-delete-button').removeAttr('disabled');
                } else {
                    $('#give-bulk-delete-button').attr('disabled', true);
                    $('#give-bulk-delete .give-donor-delete-records').removeAttr('checked');
                }
            });

            // CheckBox click event to delete records with donor.
            $body.on('click', '#give-bulk-delete .give-donor-delete-records', function () {
                if ($(this).is(':checked')) {
                    $('#give-bulk-delete .give-donor-delete-confirm').attr('checked', 'checked');
                    $('#give-bulk-delete-button').removeAttr('disabled');
                }
            });

            // Skip Donor from Bulk Delete List.
            $body.on('click', '.give-skip-donor', function () {
                const donorId = $(this).closest('div').data('id');
                $('#give-donor-' + donorId).remove();
                $('#donor-' + donorId)
                    .find('input[type="checkbox"]')
                    .removeAttr('checked');
            });

            /**
             * Clicking Event to Delete Single Donor.
             *
             * @since 2.19.0 Added donor_id as a hidden input when deleting a single donor from the table.
             */
            $body.on('click', '.give-single-donor-delete', function (e) {
                const donorId = $(this).data('id'),
                    donorSelector = $('tr#donor-' + donorId).find('.donor-selector'),
                    selectAll = $('[id^="cb-select-all-"]'),
                    bulkDeleteList = $('#give-bulk-donors'),
                    donorName = donorSelector.data('name'),
                    donorHtml =
                        '<div id="give-donor-' +
                        donorId +
                        '" data-id="' +
                        donorId +
                        '">' +
                        '<a class="give-skip-donor" title="' +
                        Give.fn.getGlobalVar('remove_from_bulk_delete') +
                        '">X</a>' +
                        donorName;

                // Reset Donors List.
                bulkDeleteList.html('');

                // Check whether the select all donor checkbox is already set, then unset it.
                if (selectAll.is(':checked')) {
                    selectAll.removeAttr('checked');
                }

                // Select the donor checkbox for which delete is clicked and others should be de-selected.
                $('.donor-selector').removeAttr('checked');
                donorSelector.prop('checked', true);

                // Add Donor to the Bulk Delete List, if donor doesn't exists in the list.
                if ($('#give-donor-' + donorId).length === 0) {
                    bulkDeleteList.prepend(donorHtml);
                    $('#give-bulk-delete').slideDown();
                }

                e.preventDefault();
            });
        },

        handleBulkActions: function (e) {
            const currentAction = $(this).closest('.tablenav').find('select').val(),
                donors = [],
                paged = $('#current-page-selector').val(),
                changedPage = GiveDonor.onLoadPageNumber !== paged,
                selectBulkActionNotice = Give.fn.getGlobalVar('donors_bulk_action.no_action_selected'),
                confirmActionNotice = Give.fn.getGlobalVar('donors_bulk_action.no_donor_selected');

            // Bailout.
            if (changedPage) {
                return true;
            }

            $.each($('.donor-selector:checked'), function () {
                donors.push($(this).val());
            });

            // If there is no bulk action selected then show an alert message.
            if ('-1' === currentAction) {
                new GiveWarningAlert({
                    modalContent: {
                        title: selectBulkActionNotice.title,
                        desc: selectBulkActionNotice.desc,
                        cancelBtnTitle: Give.fn.getGlobalVar('ok'),
                    },
                }).render();
                return false;
            }

            // If there is no donor selected then show an alert.
            if (!parseInt(donors)) {
                new GiveWarningAlert({
                    modalContent: {
                        title: confirmActionNotice.title,
                        desc: confirmActionNotice.desc,
                        cancelBtnTitle: Give.fn.getGlobalVar('ok'),
                    },
                }).render();

                return false;
            }

            if ('delete' === currentAction) {
                $('#give-bulk-delete').slideDown();
            }

            e.preventDefault();
        },
    };

    /**
     * API screen JS
     */
    const API_Screen = {
        init: function () {
            this.revoke_api_key();
            this.regenerate_api_key();
        },

        revoke_api_key: function () {
            $('body').on('click', '.give-revoke-api-key', function (e) {
                e.preventDefault();

                const url = $(this).attr('href');

                new GiveConfirmModal({
                    modalWrapper: 'give-modal--warning',
                    modalContent: {
                        desc: Give.fn.getGlobalVar('revoke_api_key'),
                    },
                    successConfirm: function () {
                        window.location.assign(url);
                    },
                }).render();

                return false;
            });
        },
        regenerate_api_key: function () {
            $('body').on('click', '.give-regenerate-api-key', function (e) {
                const url = $(this).attr('href');

                new GiveConfirmModal({
                    modalWrapper: 'give-modal--warning',
                    modalContent: {
                        desc: Give.fn.getGlobalVar('regenerate_api_key'),
                    },
                    successConfirm: function () {
                        window.location.assign(url);
                    },
                }).render();

                return false;
            });
        },
    };

    /**
     * Edit Donation form screen Js
     */
    const Edit_Form_Screen = {
        init: function () {
            const default_tab_id = $.query.get('give_tab').length ? $.query.get('give_tab') : 'form_template_options';

            this.handle_metabox_tab_click();
            this.setup_colorpicker_fields();
            this.setup_media_fields();
            this.setup_repeatable_fields();
            this.handle_repeater_group_events();

            // Multi level repeater field js.
            this.handle_multi_levels_repeater_group_events();

            // Set active tab on page load.
            this.activate_tab($('a[href="#' + default_tab_id + '"]'));
        },

        /**
         * Attach click event handler to tabs.
         */
        handle_metabox_tab_click: function () {
            const self = this;
            const $tab_links = $('.give-metabox-tabs a');

            $tab_links.on('click', function (e) {
                e.preventDefault();
                const $this = $(this);
                self.activate_tab($this);
                self.update_query($this);
            });
        },

        /**
         * Set the active tab.
         */
        activate_tab: function ($tab_link) {
            const tab_id = $tab_link.data('tab-id'),
                $li_parent = $tab_link.parent(),
                $sub_field = $('ul.give-metabox-sub-tabs', $li_parent),
                has_sub_field = $sub_field.length,
                $tab_links = $('.give-metabox-tabs a'),
                $all_tab_links_li = $tab_links.parents('li'),
                $all_sub_fields = $('ul.give-metabox-sub-tabs'),
                in_sub_fields = $tab_link.parents('ul.give-metabox-sub-tabs').length;

            // Update active tab hidden field to maintain tab after save.
            $('#give_form_active_tab').val(tab_id);

            if (has_sub_field) {
                $li_parent.toggleClass('active');
                $sub_field.removeClass('give-hidden');

                const $active_subtab_li = $('li.active', 'ul.give-metabox-sub-tabs');

                // Show hide sub fields if any and exit.
                $all_sub_fields.not($sub_field).addClass('give-hidden');
                $all_tab_links_li.not($li_parent).removeClass('active');

                $active_subtab_li.addClass('active');
            } else if (!in_sub_fields) {
                // Hide all tab and sub tabs.
                $all_tab_links_li.each(function (index, item) {
                    item = $(item);
                    item.removeClass('active');

                    if (item.hasClass('has-sub-fields')) {
                        $('ul.give-metabox-sub-tabs', item).addClass('give-hidden');
                    }
                });
            } else if (in_sub_fields) {
                // Hide all sub tabs.
                $('ul.give-metabox-sub-tabs').addClass('give-hidden');
                $all_tab_links_li.removeClass('active');

                // Hide all tab inside sub tabs.
                $tab_link
                    .parents('ul.give-metabox-sub-tabs')
                    .removeClass('give-hidden')
                    .children('li')
                    .removeClass('active');

                // Add active class to parent li.
                $tab_link.parents('li.has-sub-fields').addClass('active');
            }

            // Add active class to current tab link.
            $tab_link.parent().addClass('active');

            // Hide all tab contents.
            $('.give_options_panel').removeClass('active');

            // Show tab content.
            $($tab_link.attr('href')).addClass('active');
        },

        /**
         * Update query string with active tab ID.
         */
        update_query: function ($tab_link) {
            const tab_id = $tab_link.data('tab-id');
            const new_query = $.query.set('give_tab', tab_id).remove('message').toString();

            if (history.replaceState) {
                history.replaceState(null, null, new_query);
            }
        },

        /**
         * Initialize colorpicker.
         */
        setup_colorpicker_fields: function () {
            $(document).ready(function () {
                const $colorpicker_fields = $('.give-colorpicker');

                if ($colorpicker_fields.length) {
                    $colorpicker_fields.each(function (index, item) {
                        const $item = $(item);

                        // Bailout: do not automatically initialize color picker for repeater field group template.
                        if ($item.parents('.give-template').length) {
                            return;
                        }

                        $item.wpColorPicker();
                    });
                }
            });
        },

        setup_media_fields: function () {
            let give_media_uploader,
                $give_upload_button,
                $body = $('body');

            /**
             * Set media modal.
             */
            $body.on('click', '.give-upload-button', function (e) {
                e.preventDefault();
                let $media_modal_config = {};

                // Cache input field.
                $give_upload_button = $(this);

                // Set modal config.
                switch ($(this).data('field-type')) {
                    case 'media':
                        $media_modal_config = {
                            title: Give.fn.getGlobal().metabox_fields.media.button_title,
                            button: {text: Give.fn.getGlobal().metabox_fields.media.button_title},
                            multiple: false, // Set to true to allow multiple files to be selected.
                            library: {type: 'image'},
                        };
                        break;

                    default:
                        $media_modal_config = {
                            title: Give.fn.getGlobal().metabox_fields.file.button_title,
                            button: {text: Give.fn.getGlobal().metabox_fields.file.button_title},
                            multiple: false,
                        };
                }

                const editing = jQuery(this).closest('.give-field-wrap').find('.give-input-field').attr('editing');
                if ('undefined' !== typeof editing) {
                    wp.media.controller.Library.prototype.defaults.contentUserSetting = false;
                }

                const $library = jQuery(this).closest('.give-field-wrap').find('.give-input-field').attr('library');
                if ('undefined' !== typeof $library && '' !== $library) {
                    $media_modal_config.library = {type: $library};
                }

                // Extend the wp.media object.
                give_media_uploader = wp.media($media_modal_config);

                // When a file is selected, grab the URL and set it as the text field's value.
                give_media_uploader.on('select', function () {
                    const attachment = give_media_uploader.state().get('selection').first().toJSON(),
                        $input_field = $give_upload_button.prev(),
                        fvalue = 'id' === $give_upload_button.data('fvalue') ? attachment.id : attachment.url;

                    $body.trigger('give_media_inserted', [attachment, $input_field]);

                    // Set input field value.
                    $input_field.val(fvalue);

                    // Update attachment id field value if fvalue is not set to id.
                    if ('id' !== $give_upload_button.data('fvalue')) {
                        const attachment_id_field_name = 'input[name="' + $input_field.attr('name') + '_id"]',
                            id_field = $input_field.closest('tr').next('tr').find(attachment_id_field_name);

                        if (id_field.length) {
                            $input_field.closest('tr').next('tr').find(attachment_id_field_name).val(attachment.id);
                        }
                    }
                });

                // Open the uploader dialog.
                give_media_uploader.open();
            });

            /**
             * Show image preview.
             */
            $body.on('give_media_inserted', function (e, attachment) {
                const $parent = $give_upload_button.parents('.give-field-wrap'),
                    $image_container = $('.give-image-thumb', $parent);

                // Bailout.
                if (!$image_container.length) {
                    return false;
                }

                // Bailout and hide preview.
                if ('image' !== attachment.type) {
                    $image_container.addClass('give-hidden');
                    $('img', $image_container).attr('src', '');
                    return false;
                }

                // Set the attachment URL to our custom image input field.
                $image_container.find('img').attr('src', attachment.url);

                // Hide the add image link.
                $image_container.removeClass('give-hidden');
            });

            /**
             * Delete Image Link.
             */
            $('span.give-delete-image-thumb', '.give-image-thumb').on('click', function (event) {
                event.preventDefault();

                const $parent = $(this).parents('.give-field-wrap'),
                    $image_container = $(this).parent(),
                    $image_input_field = $('input[type="text"]', $parent);

                // Clear out the preview image.
                $image_container.addClass('give-hidden');

                // Remove image link from input field.
                $image_input_field.val('');

                // Hide the add image link.
                $('img', $image_container).attr('src', '');
            });
        },

        /**
         * Setup repeater field.
         */
        setup_repeatable_fields: function () {
            jQuery(function () {
                jQuery('.give-repeatable-field-section').each(function () {
                    const $this = $(this);

                    // Note: Do not change option params, it can break repeatable fields functionality.
                    const options = {
                        wrapper: '.give-repeatable-fields-section-wrapper',
                        container: '.container',
                        row: '.give-row',
                        add: '.give-add-repeater-field-section-row',
                        remove: '.give-remove',
                        move: '.give-move',
                        template: '.give-template',
                        confirm_before_remove_row: true,
                        confirm_before_remove_row_text: Give.fn.getGlobalVar('confirm_before_remove_row_text'),
                        is_sortable: true,
                        before_add: null,
                        after_add: handle_metabox_repeater_field_row_count,
                        //after_add:  after_add, Note: after_add is internal function in repeatable-fields.js. Uncomment this can cause of js error.
                        before_remove: null,
                        after_remove: handle_metabox_repeater_field_row_remove,
                        sortable_options: {
                            placeholder: 'give-ui-placeholder-state-highlight',
                            start: function (event, ui) {
                                // Do not allow any row at position 0.
                                if (ui.item.next().hasClass('give-template')) {
                                    ui.item.next().after(ui.item);
                                }

                                const $rows = $('.give-row', $this).not('.give-template');

                                if ($rows.length) {
                                    $rows.each(function (index, item) {
                                        // Set name for fields.
                                        const $fields = $('input[type="radio"].give-field', $(item));

                                        // Preserve radio button values.
                                        if ($fields.length) {
                                            $fields.each(function () {
                                                $(this).attr('data-give-checked', $(this).is(':checked'));
                                            });
                                        }
                                    });
                                }

                                $('body').trigger('repeater_field_sorting_start', [ui.item]);
                            },
                            stop: function (event, ui) {
                                // Do not allow any row at position 0.
                                if (ui.item.next().hasClass('give-template')) {
                                    ui.item.next().after(ui.item);
                                }

                                $('body').trigger('repeater_field_sorting_stop', [ui.item]);
                            },
                            update: function (event, ui) {
                                // Do not allow any row at position 0.
                                if (ui.item.next().hasClass('give-template')) {
                                    ui.item.next().after(ui.item);
                                }

                                const $rows = $('.give-row', $this).not('.give-template'),
                                    $container = $(this).closest('.give-repeatable-fields-section-wrapper');

                                if ($rows.length) {
                                    let row_count = 1;
                                    $rows.each(function (index, item) {
                                        // Set name for fields.
                                        const $fields = $('.give-field, label', $(item));

                                        if ($fields.length) {
                                            $fields.each(function () {
                                                const $parent = $(this).parents('.give-field-wrap'),
                                                    $currentElement = $(this);

                                                $.each(this.attributes, function (index, element) {
                                                    let old_class_name_prefix = this.value
                                                            .replace(/\[/g, '_')
                                                            .replace(/]/g, ''),
                                                        old_class_name = old_class_name_prefix + '_field',
                                                        new_class_name = '',
                                                        new_class_name_prefix = '';

                                                    // Bailout.
                                                    if (!this.value) {
                                                        return;
                                                    }

                                                    // Reorder index.
                                                    this.value = this.value.replace(
                                                        /\[\d+\]/g,
                                                        '[' + (row_count - 1) + ']'
                                                    );
                                                    new_class_name_prefix = this.value
                                                        .replace(/\[/g, '_')
                                                        .replace(/]/g, '');

                                                    // Update class name.
                                                    if ($parent.hasClass(old_class_name)) {
                                                        new_class_name = new_class_name_prefix + '_field';
                                                        $parent.removeClass(old_class_name).addClass(new_class_name);
                                                    }

                                                    // Update field id.
                                                    if (old_class_name_prefix == $currentElement.attr('id')) {
                                                        $currentElement.attr('id', new_class_name_prefix);
                                                    }
                                                });
                                            });
                                        }

                                        row_count++;
                                    });

                                    window.setTimeout(function () {
                                        // Reset radio button values.
                                        $('input[data-give-checked]', $container).each(function (index, radio) {
                                            radio = $(radio);
                                            radio.prop('checked', 'true' === radio.attr('data-give-checked'));
                                        });
                                    }, 100);

                                    // Fire event.
                                    $this.trigger('repeater_field_row_reordered', [ui.item]);
                                }
                            },
                        },
                        //row_count_placeholder: '{{row-count-placeholder}}' Note: do not modify this param otherwise it will break repeatable field functionality.
                    };

                    jQuery(this).repeatable_fields(options);
                });
            });
        },

        /**
         * Handle repeater field events.
         */
        handle_repeater_group_events: function () {
            const $repeater_fields = $('.give-repeatable-field-section'),
                $body = $('body');

            // Auto toggle repeater group
            $body.on('click', '.give-row-head .give-handlediv', function () {
                const $parent = $(this).closest('.give-row');
                $parent.toggleClass('closed');
                $('.give-row-body', $parent).toggle();
            });

            // Reset header title when new row added.
            $repeater_fields.on(
                'repeater_field_new_row_added repeater_field_row_deleted repeater_field_row_reordered',
                function () {
                    handle_repeater_group_add_number_suffix($(this));
                }
            );

            // Disable editor when sorting start.
            $body.on('repeater_field_sorting_start', function (e, row) {
                const $textarea = $('.wp-editor-area', row);

                if ($textarea.length) {
                    $textarea.each(function (index, item) {
                        window.setTimeout(function () {
                            tinyMCE.execCommand('mceRemoveEditor', true, $(item).attr('id'));
                        }, 300);
                    });
                }
            });

            // Enable editor when sorting stop.
            $body.on('repeater_field_sorting_stop', function (e, row) {
                const $textarea = $('.wp-editor-area', row);

                if ($textarea.length) {
                    $textarea.each(function (index, item) {
                        window.setTimeout(function () {
                            const textarea_id = $(item).attr('id');
                            tinyMCE.execCommand('mceAddEditor', true, textarea_id);

                            // Switch editor to tmce mode to fix some glitch which appear when you reorder rows.
                            window.setTimeout(function () {
                                // Hack to show tmce mode.
                                switchEditors.go(textarea_id, 'html');
                                $('#' + textarea_id + '-tmce').trigger('click');
                            }, 100);
                        }, 300);
                    });
                }
            });

            // Process jobs on document load for repeater fields.
            $repeater_fields.each(function (index, item) {
                // Reset title on document load for already exist groups.
                const $item = $(item);
                handle_repeater_group_add_number_suffix($item);

                // Close all tabs when page load.
                if (parseInt($item.data('close-tabs'))) {
                    $('.give-row-head button', $item).trigger('click');
                    $('.give-template', $item).removeClass('closed');
                    $('.give-template .give-row-body', $item).show();
                }
            });

            // Setup colorpicker field when row added.
            $repeater_fields.on('repeater_field_new_row_added', function (e, container, new_row) {
                $('.give-colorpicker', $(this)).each(function (index, item) {
                    const $item = $(item);

                    // Bailout: skip already init colorpocker fields.
                    if ($item.parents('.wp-picker-container').length || $item.parents('.give-template').length) {
                        return;
                    }

                    $item.wpColorPicker();
                });

                // Load WordPress editor by ajax.
                const wysiwyg_editor_container = $('div[data-wp-editor]', new_row);

                if (wysiwyg_editor_container.length) {
                    wysiwyg_editor_container.each(function (index, item) {
                        const $item = $(item),
                            wysiwyg_editor = $('.wp-editor-wrap', $item),
                            textarea = $('textarea', $item),
                            textarea_id = 'give_wysiwyg_unique_' + Math.random().toString().replace('.', '_'),
                            wysiwyg_editor_label = wysiwyg_editor.prev();

                        textarea.attr('id', textarea_id);

                        $.post(
                            ajaxurl,
                            {
                                action: 'give_load_wp_editor',
                                wp_editor: $item.data('wp-editor'),
                                wp_editor_id: textarea_id,
                                textarea_name: $('textarea', $item).attr('name'),
                            },
                            function (res) {
                                wysiwyg_editor.remove();
                                wysiwyg_editor_label.after(res);

                                // Setup qt data for editor.
                                tinyMCEPreInit.qtInit[textarea.attr('id')] = $.extend(
                                    true,
                                    tinyMCEPreInit.qtInit._give_agree_text,
                                    {id: textarea_id}
                                );

                                // Setup mce data for editor.
                                tinyMCEPreInit.mceInit[textarea_id] = $.extend(
                                    true,
                                    tinyMCEPreInit.mceInit._give_agree_text,
                                    {
                                        body_class:
                                            textarea_id +
                                            ' post-type-give_forms post-status-publish locale-' +
                                            tinyMCEPreInit.mceInit._give_agree_text['wp_lang_attr'].toLowerCase(),
                                        selector: '#' + textarea_id,
                                    }
                                );

                                // Setup editor.
                                tinymce.init(tinyMCEPreInit.mceInit[textarea_id]);
                                quicktags(tinyMCEPreInit.qtInit[textarea_id]);
                                QTags._buttonsInit();

                                window.setTimeout(function () {
                                    // Hack to show tmce mode.
                                    switchEditors.go(textarea_id, 'html');
                                    $('#' + textarea_id + '-tmce').trigger('click');
                                }, 100);

                                if (!window.wpActiveEditor) {
                                    window.wpActiveEditor = textarea_id;
                                }
                            }
                        );
                    });
                }
            });
        },

        /**
         *  Handle events for multi level repeater group.
         */
        handle_multi_levels_repeater_group_events: function () {
            const $repeater_fields = $('#_give_donation_levels_field');

            // Add level title as suffix to header title when admin add level title.
            $('body').on('keyup', '.give-multilevel-text-field', function () {
                const $parent = $(this).closest('tr'),
                    $header_title_container = $('.give-row-head h2 span', $parent),
                    donation_level_header_text_prefix = $header_title_container.data('header-title');

                // Donation level header already set.
                if ($(this).val() && $(this).val() === $header_title_container.html()) {
                    return false;
                }

                if ($(this).val()) {
                    // Change donaiton level header text.
                    $header_title_container.html(donation_level_header_text_prefix + ': ' + $(this).val());
                } else {
                    // Reset donation level header heading text.
                    $header_title_container.html(donation_level_header_text_prefix);
                }
            });

            //  Add level title as suffix to header title on document load.
            $('.give-multilevel-text-field').each(function (index, item) {
                // Skip first element.
                if (!index) {
                    return;
                }

                // Check if item is jquery object or not.
                const $item = $(item);

                const $parent = $item.closest('tr'),
                    $header_title_container = $('.give-row-head h2 span', $parent),
                    donation_level_header_text_prefix = $header_title_container.data('header-title');

                // Donation level header already set.
                if ($item.val() && $item.val() === $header_title_container.html()) {
                    return false;
                }

                if ($item.val()) {
                    // Change donaiton level header text.
                    $header_title_container.html(donation_level_header_text_prefix + ': ' + $item.val());
                } else {
                    // Reset donation level header heading text.
                    $header_title_container.html(donation_level_header_text_prefix);
                }
            });

            // Handle row deleted event for levels repeater field.
            $repeater_fields.on('repeater_field_row_deleted', function () {
                const $this = $(this);

                window.setTimeout(function () {
                    const $parent = $this,
                        $repeatable_rows = $('.give-row', $parent).not('.give-template'),
                        $default_radio = $('.give-give_default_radio_inline', $repeatable_rows),
                        number_of_level = $repeatable_rows.length;

                    if (number_of_level === 1) {
                        $default_radio.prop('checked', true);
                    }
                }, 200);
            });

            // Handle row added event for levels repeater field.
            $repeater_fields.on('repeater_field_new_row_added', function (e, container, new_row) {
                let $this = $(this),
                    max_level_id = 0;

                // Auto set default level if no level set as default.
                window.setTimeout(function () {
                    // Set first row as default if selected default row deleted.
                    // When a row is removed containing the default selection then revert default to first repeatable row.
                    if ($('.give-give_default_radio_inline', $this).is(':checked') === false) {
                        $('.give-row', $this)
                            .not('.give-template')
                            .first()
                            .find('.give-give_default_radio_inline')
                            .prop('checked', true);
                    }
                }, 200);

                // Get max level id.
                $('input[type="hidden"].give-levels_id', $this).each(function (index, item) {
                    const $item = $(item),
                        current_level = parseInt($item.val());
                    if (max_level_id < current_level) {
                        max_level_id = current_level;
                    }
                });

                // Auto set level id for new setting level setting group.
                $('input[type="hidden"].give-levels_id', new_row).val(++max_level_id);
            });
        },
    };

    /**
     * Handle row count and field count for repeatable field.
     */
    var handle_metabox_repeater_field_row_count = function (container, new_row) {
        let row_count = $(container).attr('data-rf-row-count'),
            $container = $(container),
            $parent = $container.parents('.give-repeatable-field-section');

        row_count++;

        // Set name for fields.
        $('*', new_row).each(function () {
            $.each(this.attributes, function (index, element) {
                this.value = this.value.replace('{{row-count-placeholder}}', row_count - 1);
            });
        });

        // Set row counter.
        $(container).attr('data-rf-row-count', row_count);

        // Fire event: Row added.
        $parent.trigger('repeater_field_new_row_added', [container, new_row]);
    };

    /**
     * Handle row remove for repeatable field.
     */
    var handle_metabox_repeater_field_row_remove = function (container) {
        let $container = $(container),
            $parent = $container.parents('.give-repeatable-field-section'),
            row_count = $(container).prop('data-rf-row-count');

        // Reduce row count.
        $container.prop('data-rf-row-count', --row_count);

        // Fire event: Row deleted.
        $parent.trigger('repeater_field_row_deleted');
    };

    /**
     * Add number suffix to repeater group.
     */
    var handle_repeater_group_add_number_suffix = function ($parent) {
        // Bailout: check if auto group numbering is on or not.
        if (!parseInt($parent.data('group-numbering'))) {
            return;
        }

        const $header_title_container = $('.give-row-head h2 span', $parent),
            header_text_prefix = $header_title_container.data('header-title');

        $header_title_container.each(function (index, item) {
            const $item = $(item);

            // Bailout: do not rename header title in fields template.
            if ($item.parents('.give-template').length) {
                return;
            }

            $item.html(header_text_prefix + ': ' + index);
        });
    };

    /**
     * Payment history listing page js
     */
    var GivePaymentHistory = {
        onLoadPageNumber: '',

        init: function () {
            GivePaymentHistory.onLoadPageNumber = $('#current-page-selector').val();
            $('body').on('click', '#give-payments-filter input[type="submit"]', this.handleBulkActions);
        },

        handleBulkActions: function (e) {
            let currentAction = $(this).closest('.tablenav').find('select').val(),
                currentActionLabel = $(this)
                    .closest('.tablenav')
                    .find('option[value="' + currentAction + '"]')
                    .text(),
                $payments = $('input[name="payment[]"]:checked').length,
                isStatusTypeAction = -1 !== currentAction.indexOf('set-status-'),
                confirmActionNotice = '',
                status = '',
                paged = $('#current-page-selector').val(),
                changedPage = GivePaymentHistory.onLoadPageNumber !== paged;

            // Bailout.
            if (changedPage) {
                return true;
            }

            // Set common action, if action type is status.
            currentAction = isStatusTypeAction ? 'set-to-status' : currentAction;

            if ('-1' === currentAction) {
                new GiveWarningAlert({
                    modalContent: {
                        title: Give.fn.getGlobal().donors_bulk_action.no_action_selected.title,
                        desc: Give.fn.getGlobal().donors_bulk_action.no_action_selected.desc,
                        cancelBtnTitle: Give.fn.getGlobalVar('ok'),
                    },
                }).render();
                return false;
            }

            if (Object.keys(Give.fn.getGlobalVar('donations_bulk_action')).length) {
                for (status in Give.fn.getGlobalVar('donations_bulk_action')) {
                    if (status === currentAction) {
                        // Get status text if current action types is status.
                        confirmActionNotice = isStatusTypeAction
                            ? Give.fn
                                  .getGlobal()
                                  .donations_bulk_action[currentAction].zero.replace(
                                      '{status}',
                                      currentActionLabel.replace('Set To ', '')
                                  )
                            : Give.fn.getGlobal().donations_bulk_action[currentAction].zero;

                        // Check if admin selected any donations or not.
                        if (!parseInt($payments)) {
                            new GiveWarningAlert({
                                modalContent: {
                                    title: Give.fn.getGlobal().donations_bulk_action.titles.zero,
                                    desc: confirmActionNotice,
                                    cancelBtnTitle: Give.fn.getGlobalVar('ok'),
                                },
                            }).render();
                            return false;
                        }

                        // Get message on basis of payment count.
                        confirmActionNotice =
                            1 < $payments
                                ? Give.fn.getGlobal().donations_bulk_action[currentAction].multiple
                                : Give.fn.getGlobal().donations_bulk_action[currentAction].single;

                        e.preventDefault();

                        new GiveConfirmModal({
                            modalContent: {
                                title: Give.fn.getGlobalVar('confirm_bulk_action'),
                                desc: confirmActionNotice
                                    .replace('{payment_count}', $payments)
                                    .replace('{status}', currentActionLabel.replace('Set To ', '')),
                            },
                            successConfirm: function (args) {
                                $('#give-payments-filter').submit();
                            },
                        }).render();
                    }
                }
            }

            return true;
        },
    };

    const GiveShortcodeButtonObj = {
        init: function () {
            // Run scripts for shortcode buttons.
            const shorcodeButtonEls = document.querySelectorAll('.js-give-shortcode-button');
            if (shorcodeButtonEls) {
                for (const buttonEl of shorcodeButtonEls) {
                    const shortcodeButton = new GiveShortcodeButton(buttonEl);
                    shortcodeButton.init();
                }
            }
        },
    };

    /**
     * Keep multi select options order.
     *
     * @since 2.8.0
     */
    const GiveMultiSelectOptions = {
        init: function () {
            const selectChosen = document.querySelectorAll('.give-select-chosen[multiple]') ?? [];

            Array.from(selectChosen).forEach((dropdown) => {
                const order = dropdown.dataset.order ? dropdown.dataset.order.split('|') : [];

                if (order.length > 0) {
                    GiveMultiSelectOptions.reorderItems(dropdown, order);
                }

                // Update order on change
                $(dropdown)
                    .chosen()
                    .change(function (e, currentOption) {
                        const dropdown = e.target;
                        const orderedOptions = [];

                        if (currentOption.deselected) {
                            $(this).trigger('chosen:updated');
                        }

                        const items = document.querySelectorAll(`#${dropdown.id}_chosen li.search-choice`) ?? [];

                        items.forEach((item) => {
                            const text = item.querySelector('span').textContent;
                            const option = Object.values(dropdown.options).find(
                                (option) => option.textContent === text
                            );

                            if (option) {
                                orderedOptions.push({
                                    text: option.textContent,
                                    value: option.value,
                                    selected: true,
                                });
                            }
                        });

                        // Fill in rest of the options
                        Object.values(dropdown.options).map((option) => {
                            const included = orderedOptions.filter(
                                (orderedOption) => orderedOption.text === option.textContent
                            ).length;
                            if (!included) {
                                orderedOptions.push({
                                    text: option.textContent,
                                    value: option.value,
                                    selected: false,
                                });
                            }
                        });

                        // Rebuild the dropdown
                        GiveMultiSelectOptions.rebuildDropDown(dropdown, orderedOptions);
                    });
            });
        },

        reorderItems: function (dropdown, order) {
            const options = dropdown.options;
            const orderedOptions = [];

            order.forEach((value, i) => {
                const items = document.querySelectorAll(`#${dropdown.id}_chosen li.search-choice`) ?? [];

                items.forEach((item, j) => {
                    if (i === j) {
                        const option = Object.values(options).find((option) => option.value === value);

                        item.querySelector('span').textContent = option.text;

                        orderedOptions.push({
                            value: option.value,
                            text: option.text,
                            selected: true,
                        });
                    }
                });
            });

            // Fill in rest of the options
            Object.values(options).map((option) => {
                const included = orderedOptions.filter((orderedOption) => orderedOption.value === option.value).length;
                if (!included) {
                    orderedOptions.push({
                        value: option.value,
                        text: option.textContent,
                        selected: option.selected,
                    });
                }
            });

            // Rebuild the dropdown
            GiveMultiSelectOptions.rebuildDropDown(dropdown, orderedOptions);

            $(this).trigger('chosen:updated');
        },

        rebuildDropDown: function (dropdown, options) {
            dropdown.innerHTML = '';
            options.map((option) => {
                const newOption = document.createElement('option');
                newOption.value = option.value;
                newOption.textContent = option.text;
                if (option.selected) {
                    newOption.setAttribute('selected', 'true');
                }
                dropdown.add(newOption);
            });
        },
    };

    // On DOM Ready.
    $(function () {
        give_dismiss_notice();
        enable_admin_datepicker();
        handle_status_change();
        setup_chosen_give_selects();
        $.giveAjaxifyFields({type: 'country_state', debug: true});
        GiveListDonation.init();
        Give_Edit_Donation.init();
        Give_Settings.init();
        Give_Reports.init();
        GiveDonor.init();
        API_Screen.init();
        Give_Export.init();
        Give_Updates.init();
        Give_Upgrades.init();
        Edit_Form_Screen.init();
        GivePaymentHistory.init();
        GiveShortcodeButtonObj.init();
        GiveMultiSelectOptions.init();

        // Footer.
        $('a.give-rating-link').click(function () {
            jQuery(this).parent().text(jQuery(this).data('rated'));
        });

        // Ajax user search.
        $('.give-ajax-user-search').on('keyup', function () {
            const user_search = $(this).val();
            let exclude = '';

            if ($(this).data('exclude')) {
                exclude = $(this).data('exclude');
            }

            $('.give-ajax').show();
            data = {
                action: 'give_search_users',
                user_name: user_search,
                exclude: exclude,
            };

            document.body.style.cursor = 'wait';

            $.ajax({
                type: 'POST',
                data: data,
                dataType: 'json',
                url: ajaxurl,
                success: function (search_response) {
                    $('.give-ajax').hide();
                    $('.give_user_search_results').removeClass('hidden');
                    $('.give_user_search_results span').html('');
                    $(search_response.results).appendTo('.give_user_search_results span');
                    document.body.style.cursor = 'default';
                },
            });
        });

        $('body').on('click.giveSelectUser', '.give_user_search_results span a', function (e) {
            e.preventDefault();
            const login = $(this).data('login');
            $('.give-ajax-user-search').val(login);
            $('.give_user_search_results').addClass('hidden');
            $('.give_user_search_results span').html('');
        });

        $('body').on('click.giveCancelUserSearch', '.give_user_search_results a.give-ajax-user-cancel', function (e) {
            e.preventDefault();
            $('.give-ajax-user-search').val('');
            $('.give_user_search_results').addClass('hidden');
            $('.give_user_search_results span').html('');
        });

        let $poststuff = $('#poststuff'),
            thousand_separator = Give.fn.getGlobalVar('thousands_separator'),
            decimal_separator = Give.fn.getGlobalVar('decimal_separator'),
            thousand_separator_count = '',
            alphabet_count = '',
            price_string = '',
            // Thousand separation limit in price depends upon decimal separator symbol.
            // If thousand separator is equal to decimal separator then price does not have more then 1 thousand separator otherwise limit is zero.
            thousand_separator_limit = decimal_separator === thousand_separator ? 1 : 0;

        // Check & show message on keyup event.
        $poststuff.on('keyup', 'input.give-money-field, input.give-price-field', function () {
            const tootltip_setting = {
                label: Give.fn.getGlobalVar('price_format_guide').trim(),
            };

            // Count thousand separator in price string.
            thousand_separator_count = ($(this).val().match(new RegExp(thousand_separator, 'g')) || []).length;
            alphabet_count = ($(this).val().match(new RegExp('[a-z]', 'g')) || []).length;

            // Show qtip conditionally if thousand separator detected on price string.
            if (
                -1 !== $(this).val().indexOf(thousand_separator) &&
                thousand_separator_limit < thousand_separator_count
            ) {
                $(this).giveHintCss('show', tootltip_setting);
            } else if (alphabet_count) {
                $(this).giveHintCss('show', tootltip_setting);
            } else {
                $(this).giveHintCss('hide', tootltip_setting);
            }

            // Reset thousand separator count.
            thousand_separator_count = alphabet_count = '';
        });

        // Format price sting of input field on focusout.
        $poststuff.on('focusout', 'input.give-money-field, input.give-price-field', function () {
            price_string = give_unformat_currency($(this).val(), false);

            $(this).giveHintCss('hide', {label: Give.fn.getGlobalVar('price_format_guide').trim()});

            // Back out.
            if (give_unformat_currency('0', false) === give_unformat_currency($(this).val(), false)) {
                let default_amount = $(this).attr('placeholder');
                default_amount = !default_amount ? '0' : default_amount;

                $(this).val(default_amount);

                return false;
            }

            // Replace dot decimal separator with user defined decimal separator.
            price_string = price_string.replace('.', decimal_separator);

            // Check if current number is negative or not.
            if (-1 !== price_string.indexOf('-')) {
                price_string = price_string.replace('-', '');
            }

            // Update format price string in input field.
            $(this).val(price_string);
        });

        // Set default value to 1 even if user inputs empty or negative number of donations.
        $poststuff.on('focusout', '#_give_number_of_donation_goal', function () {
            if (1 > $(this).val()) {
                $(this).val(1);
            }
        });

        /**
         * Responsive setting tab features.
         */

        // Show/Hide sub tab nav.
        $('.give-settings-page')
            .on('click', '#give-show-sub-nav', function (e) {
                e.preventDefault();

                const $sub_tab_nav = $(this).next();

                if (!$sub_tab_nav.is(':hover')) {
                    $sub_tab_nav.toggleClass('give-hidden');
                }

                return false;
            })
            .on('blur', '#give-show-sub-nav', function () {
                const $sub_tab_nav = $(this).next();

                if (!$sub_tab_nav.is(':hover')) {
                    $sub_tab_nav.addClass('give-hidden');
                }
            });

        /**
         * Automatically show/hide email setting fields.
         */
        $('.give_email_api_notification_status_setting input').change(function () {
            // Bailout.
            let value = $(this).val(),
                is_enabled = 'enabled' === value,
                $setting_fields = {};

            // Get setting fields.
            if ($(this).closest('.give_options_panel').length) {
                $setting_fields = $(this)
                    .closest('.give_options_panel')
                    .children(
                        '.give-field-wrap:not(.give_email_api_notification_status_setting), .give-repeatable-field-section'
                    );
            } else if ($(this).closest('table').length) {
                $setting_fields = $(this).closest('table').find('tr:not(.give_email_api_notification_status_setting)');
            }

            if (-1 === jQuery.inArray(value, ['enabled', 'disabled', 'global'])) {
                return false;
            }

            // Bailout.
            if (!$setting_fields.length) {
                return false;
            }

            // Show hide setting fields.
            is_enabled ? $setting_fields.show() : $setting_fields.hide();
        });

        $('.give_email_api_notification_status_setting input:checked').change();

        // Render setting tab.
        give_render_responsive_tabs();
    });
})(jQuery);

/**
 * Responsive js.
 */
jQuery(window).resize(function () {
    give_render_responsive_tabs();
});

/**
 * Render responsive tabs
 */
function give_render_responsive_tabs() {
    let $setting_page_form = jQuery('.give-settings-page'),
        $main_tab_nav = jQuery('.give-nav-tab-wrapper'),
        setting_page_form_width = $setting_page_form.width(),
        $sub_tab_nav_wrapper = jQuery('.give-sub-nav-tab-wrapper'),
        $sub_tab_nav = jQuery('nav', $sub_tab_nav_wrapper),
        $setting_tab_links = jQuery('.give-nav-tab-wrapper > a:not(give-not-tab)'),
        $show_tabs = [],
        $hide_tabs = [],
        tab_width = 0;

    if (600 < jQuery(window).outerWidth()) {
        tab_width = 200;
    }

    // Bailout.
    if (!$setting_page_form.length) {
        return false;
    }

    // Update tab wrapper css.
    $main_tab_nav.css({
        height: 'auto',
        overflow: 'visible',
    });

    // Show all tab if anyone hidden to calculate correct tab width.
    $setting_tab_links.removeClass('give-hidden');

    const refactor_tabs = new Promise(function (resolve, reject) {
        // Collect tabs to show or hide.
        jQuery.each($setting_tab_links, function (index, $tab_link) {
            $tab_link = jQuery($tab_link);
            tab_width = tab_width + parseInt($tab_link.outerWidth());

            if (tab_width < setting_page_form_width) {
                $show_tabs.push($tab_link);
            } else {
                $hide_tabs.push($tab_link);
            }
        });

        resolve(true);
    });

    refactor_tabs.then(function (is_refactor_tabs) {
        // Remove current tab from sub menu and add this to main menu if exist and get last tab from main menu and add this to sub menu.
        if ($hide_tabs.length && -1 !== window.location.search.indexOf('&tab=')) {
            let $current_tab_nav = {},
                query_params = get_url_params();

            $hide_tabs = $hide_tabs.filter(function ($tab_link) {
                const is_current_nav_item = -1 !== parseInt($tab_link.attr('href').indexOf('&tab=' + query_params.tab));

                if (is_current_nav_item) {
                    $current_tab_nav = $tab_link;
                }

                return !is_current_nav_item;
            });

            if ($current_tab_nav.length) {
                $hide_tabs.unshift($show_tabs.pop());
                $show_tabs.push($current_tab_nav);
            }
        }

        const show_tabs = new Promise(function (resolve, reject) {
            // Show main menu tabs.
            if ($show_tabs.length) {
                jQuery.each($show_tabs, function (index, $tab_link) {
                    $tab_link = jQuery($tab_link);

                    if ($tab_link.hasClass('give-hidden')) {
                        $tab_link.removeClass('give-hidden');
                    }
                });
            }

            resolve(true);
        });

        show_tabs.then(function (is_show_tabs) {
            // Hide sub menu tabs.
            if ($hide_tabs.length) {
                $sub_tab_nav.html('');

                jQuery.each($hide_tabs, function (index, $tab_link) {
                    $tab_link = jQuery($tab_link);
                    if (!$tab_link.hasClass('nav-tab-active')) {
                        $tab_link.addClass('give-hidden');
                    }
                    $tab_link.clone().removeClass().appendTo($sub_tab_nav);
                });

                if (!jQuery('.give-sub-nav-tab-wrapper', $main_tab_nav).length) {
                    $main_tab_nav.append($sub_tab_nav_wrapper);
                }

                $sub_tab_nav_wrapper.show();
            } else {
                $sub_tab_nav_wrapper.hide();
            }
        });
    });
}

/**
 * Get url query params.
 *
 * @returns {Array}
 */
function get_url_params() {
    let vars = [],
        hash;
    const hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (let i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars[hash[0]] = hash[1];
    }
    return vars;
}
