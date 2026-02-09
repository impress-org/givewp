(function ($) {
    'use strict';

    if (typeof wp === 'undefined' || typeof wp.i18n === 'undefined') {
        console.error('wp.i18n is not available. Make sure wp-i18n is loaded as a dependency.');
        return;
    }

    const {__, _n, sprintf} = wp.i18n;

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(
            function () {
                alert(__('Code copied to clipboard!', 'give'));
            },
            function (err) {
                console.error('Could not copy text: ', err);
                alert(__('Failed to copy code to clipboard.', 'give'));
            }
        );
    }

    function showStatusMessage(message, type) {
        const $message = $('#giveTgbStatusMessage');
        $message
            .removeClass()
            .addClass('give-tgb-status ' + type)
            .html(message)
            .show();
    }

    function handleTabNavigation() {
        $('.give-tgb-tab-button').on('click', function () {
            const tabName = $(this).data('tab');

            $('.give-tgb-tab-button').removeClass('active');
            $(this).addClass('active');

            $('.give-tgb-tab-panel').removeClass('active');
            $('#tab-' + tabName).addClass('active');

            if (tabName === 'new') {
                $('#new-organization-form').show();
                $('#existing-organization-form').hide();
            } else if (tabName === 'existing') {
                $('#new-organization-form').hide();
                $('#existing-organization-form').show();
            }
        });
    }

    window.refreshOrganizationData = function (organizationId) {
        if (
            !confirm(
                __(
                    'Are you sure you want to refresh the organization data from the API? This will update the saved data.',
                    'give'
                )
            )
        ) {
            return;
        }

        const $button = $('button[onclick*="refreshOrganizationData"]');
        const originalText = $button.html();
        $button
            .prop('disabled', true)
            .html(
                '<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> ' +
                    __('Refreshing...', 'give')
            );

        $.ajax({
            url: giveTgbSettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'giveTgbRefreshOrganization',
                organizationId: organizationId,
                nonce: giveTgbSettings.nonce,
            },
            success: function (response) {
                if (response.success) {
                    $(window).off('beforeunload');
                    window.location.reload();
                } else {
                    alert(
                        sprintf(
                            __('Failed to refresh data: %s', 'give'),
                            response.data.message || __('Unknown error', 'give')
                        )
                    );
                    $button.prop('disabled', false).html(originalText);
                }
            },
            error: function () {
                alert(__('Failed to refresh data. Please try again.', 'give'));
                $button.prop('disabled', false).html(originalText);
            },
        });
    };

    function handleOnboardingSubmit() {
        const $form = $('#giveTgbOnboardingForm');
        const $container = $('#new-organization-form');
        const $btn = $('#submitOnboarding');
        const $message = $('#giveTgbOnboardingMessage');

        $btn.prop('disabled', true).val(__('Submitting...', 'give'));
        $message.hide().removeClass('give-tgb-status success error');

        // Serialize by container: form may be nested inside #give-mainform, so browser can break
        // the inner form and .serialize() on $form would miss inputs; container always has them.
        let formData = $container.find('input, select, textarea').serialize();
        formData += (formData ? '&' : '') + 'action=giveTgbOnboarding';
        formData += '&nonce=' + encodeURIComponent(giveTgbSettings.nonce);

        $.ajax({
            url: giveTgbSettings.ajaxurl,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('give-tgb-status success')
                        .text(response.data.message)
                        .show();
                    if ($form.length && $form[0] && typeof $form[0].reset === 'function') {
                        $form[0].reset();
                    } else {
                        $container.find('input, select, textarea').each(function () {
                            const $el = $(this);
                            if ($el.attr('type') === 'checkbox' || $el.attr('type') === 'radio') {
                                $el.prop('checked', false);
                            } else {
                                $el.val('');
                            }
                        });
                    }

                    if (response.data && Array.isArray(response.data.warnings) && response.data.warnings.length > 0) {
                        const warningsHtml =
                            '<div class="notice notice-warning" style="margin-top:10px;">' +
                            '<p><strong>' +
                            __('Some payment channel onboardings did not complete:', 'give') +
                            '</strong></p>' +
                            '<ul style="margin-left: 18px;">' +
                            response.data.warnings
                                .map(function (w) {
                                    return '<li>' + w + '</li>';
                                })
                                .join('') +
                            '</ul>' +
                            '</div>';
                        $(warningsHtml).insertAfter($message);
                    }

                    if (response.data.reload && giveTgbSettings.getStartedUrl) {
                        setTimeout(function () {
                            $(window).off('beforeunload');
                            window.location.href = giveTgbSettings.getStartedUrl;
                        }, 3000);
                    }
                } else {
                    let errorHtml = response.data.message || __('Unknown error occurred', 'give');

                    // If response object is available, display it for debugging
                    if (response.data.response) {
                        errorHtml +=
                            '<br><br><details style="margin-top: 20px; display: block;" class="error-details"><summary style="cursor: pointer; font-weight: bold; color: #856404; padding: 8px 0; display: flex; align-items: center; gap: 6px;"><span class="details-icon">▶</span> ' +
                            __('View error details', 'give') +
                            '</summary>';
                        errorHtml +=
                            '<div style="margin-top: 15px; padding: 12px; background: #fff; border: 1px solid #ddd; border-radius: 4px; max-height: 500px; overflow-y: auto; overflow-x: auto;">';
                        errorHtml +=
                            '<pre style="margin: 0; padding: 0; font-family: monospace; font-size: 11px; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; color: #333;">';
                        errorHtml += JSON.stringify(response.data.response, null, 2);
                        errorHtml += '</pre></div></details>';

                        // Add CSS for rotating arrow icon
                        errorHtml +=
                            '<style>details.error-details summary .details-icon { display: inline-block; transition: transform 0.2s; } details.error-details[open] summary .details-icon { transform: rotate(90deg); }</style>';
                    }

                    $message
                        .removeClass('success')
                        .addClass('give-tgb-status error')
                        .html(errorHtml)
                        .css('margin-top', '15px')
                        .show();
                }
            },
            error: function () {
                showStatusMessage(
                    '<strong>' +
                        __('❌ Onboarding Failed', 'give') +
                        '</strong><p>' +
                        __('An error occurred during submission.', 'give') +
                        '</p>',
                    'error'
                );
            },
            complete: function () {
                $btn.prop('disabled', false).val(__('Submit Organization for Onboarding', 'give'));
            },
        });
    }

    function handleExistingOrganizationSubmit() {
        const $form = $('#giveTgbExistingOrganizationForm');
        const $container = $('#existing-organization-form');
        const $submitBtn = $('#submitExistingOrganization');
        const $message = $('#giveTgbExistingOrganizationMessage');

        $submitBtn.prop('disabled', true).val(__('Connecting...', 'give'));
        $message.hide().removeClass('give-tgb-status success error');

        // Serialize by container so inputs are included when form is nested inside #give-mainform.
        const formData = $container.find('input, select, textarea').serializeArray();
        formData.push({name: 'action', value: 'giveTgbConnectExisting'});
        formData.push({name: 'nonce', value: giveTgbSettings.nonce});

        $.ajax({
            url: giveTgbSettings.ajaxurl,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('give-tgb-status success')
                        .text(response.data.message)
                        .show();
                    if ($form.length && $form[0] && typeof $form[0].reset === 'function') {
                        $form[0].reset();
                    } else {
                        $container.find('input, select, textarea').each(function () {
                            const $el = $(this);
                            if ($el.attr('type') === 'checkbox' || $el.attr('type') === 'radio') {
                                $el.prop('checked', false);
                            } else {
                                $el.val('');
                            }
                        });
                    }

                    if (response.data && Array.isArray(response.data.warnings) && response.data.warnings.length > 0) {
                        const warningsHtml =
                            '<div class="notice notice-warning" style="margin-top:10px;">' +
                            '<p><strong>' +
                            __('Some payment channel onboardings did not complete:', 'give') +
                            '</strong></p>' +
                            '<ul style="margin-left: 18px;">' +
                            response.data.warnings
                                .map(function (w) {
                                    return '<li>' + w + '</li>';
                                })
                                .join('') +
                            '</ul>' +
                            '</div>';
                        $(warningsHtml).insertAfter($message);
                    }

                    if (response.data.reload && giveTgbSettings.getStartedUrl) {
                        setTimeout(function () {
                            $(window).off('beforeunload');
                            window.location.href = giveTgbSettings.getStartedUrl;
                        }, 3000);
                    }
                } else {
                    let errorHtml = response.data.message || __('Unknown error occurred', 'give');

                    // If response object is available, display it for debugging
                    if (response.data.response) {
                        errorHtml +=
                            '<br><br><details style="margin-top: 20px; display: block;" class="error-details"><summary style="cursor: pointer; font-weight: bold; color: #856404; padding: 8px 0; display: flex; align-items: center; gap: 6px;"><span class="details-icon">▶</span> ' +
                            __('View error details', 'give') +
                            '</summary>';
                        errorHtml +=
                            '<div style="margin-top: 15px; padding: 12px; background: #fff; border: 1px solid #ddd; border-radius: 4px; max-height: 500px; overflow-y: auto; overflow-x: auto;">';
                        errorHtml +=
                            '<pre style="margin: 0; padding: 0; font-family: monospace; font-size: 11px; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; color: #333;">';
                        errorHtml += JSON.stringify(response.data.response, null, 2);
                        errorHtml += '</pre></div></details>';

                        // Add CSS for rotating arrow icon
                        errorHtml +=
                            '<style>details.error-details summary .details-icon { display: inline-block; transition: transform 0.2s; } details.error-details[open] summary .details-icon { transform: rotate(90deg); }</style>';
                    }

                    $message
                        .removeClass('success')
                        .addClass('give-tgb-status error')
                        .html(errorHtml)
                        .css('margin-top', '15px')
                        .show();
                }
            },
            error: function () {
                $message.addClass('give-tgb-status error').html(__('Failed to connect organization.', 'give')).show();
            },
            complete: function () {
                $submitBtn.prop('disabled', false).val(__('Connect Organization', 'give'));
            },
        });
    }

    function initSettingsPage() {
        handleTabNavigation();

        $(document).on('submit', '#giveTgbOnboardingForm', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleOnboardingSubmit();
        });

        $(document).on('click', '#submitOnboarding', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleOnboardingSubmit();
        });

        $(document).on('keydown', '#giveTgbOnboardingForm', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                e.stopPropagation();
                handleOnboardingSubmit();
            }
        });

        $(document).on('submit', '#giveTgbExistingOrganizationForm', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleExistingOrganizationSubmit();
        });

        $(document).on('click', '#submitExistingOrganization', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleExistingOrganizationSubmit();
        });

        $(document).on('keydown', '#giveTgbExistingOrganizationForm', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                e.stopPropagation();
                handleExistingOrganizationSubmit();
            }
        });

        const activeTab = $('.give-tgb-tab-button.active').data('tab');
        if (activeTab === 'new') {
            $('#new-organization-form').show();
            $('#existing-organization-form').hide();
        } else if (activeTab === 'existing') {
            $('#new-organization-form').hide();
            $('#existing-organization-form').show();
        }
    }

    $(document).ready(function () {
        initSettingsPage();
    });

    window.copyToClipboard = copyToClipboard;

    function copyOrganizationId(organizationId) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard
                .writeText(organizationId)
                .then(function () {
                    showCopySuccess();
                })
                .catch(function (err) {
                    console.error('Failed to copy: ', err);
                    fallbackCopyToClipboard(organizationId);
                });
        } else {
            fallbackCopyToClipboard(organizationId);
        }
    }

    function fallbackCopyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess();
            } else {
                showCopyError();
            }
        } catch (err) {
            console.error('Fallback copy failed: ', err);
            showCopyError();
        }

        document.body.removeChild(textArea);
    }

    function showCopySuccess() {
        alert(__('Organization ID copied to clipboard!', 'give'));
    }

    function showCopyError() {
        alert(__('Failed to copy. Please try again.', 'give'));
    }

    window.copyOrganizationId = copyOrganizationId;

    function disconnectOrganization() {
        if (
            !confirm(
                __(
                    'Are you sure you want to disconnect this organization? This will remove the connection and you will need to reconnect or create a new organization.',
                    'give'
                )
            )
        ) {
            return;
        }

        const $button = $('.disconnect-btn');
        const $buttonText = $button.find('.button-text');
        const $buttonIcon = $button.find('.dashicons');
        const originalText = $buttonText.text();
        const originalIcon = $buttonIcon.attr('class');

        $button.prop('disabled', true);
        $buttonText.text(__('Disconnecting...', 'give'));
        $buttonIcon.attr('class', 'dashicons dashicons-update');
        $button.addClass('loading');

        const $statusDiv = $(
            '<div class="disconnect-status" style="margin-top: 10px; padding: 8px; background: #f0f0f0; border-radius: 3px; font-size: 12px; color: #666;">' +
                __('Disconnecting organization from The Giving Block...', 'give') +
                '</div>'
        );
        $button.closest('div').append($statusDiv);

        $.ajax({
            url: giveTgbSettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'giveTgbDisconnectOrganization',
                nonce: giveTgbSettings.nonce,
            },
            success: function (response) {
                if (response.success) {
                    $statusDiv.html(
                        '<span style="color: #46b450;">' +
                            __('✓ Organization disconnected successfully! Refreshing page...', 'give') +
                            '</span>'
                    );
                    setTimeout(function () {
                        $(window).off('beforeunload');
                        window.location.reload();
                    }, 1500);
                } else {
                    $statusDiv.html(
                        '<span style="color: #dc3232;">' +
                            sprintf(__('✗ Error: %s', 'give'), response.data.message || __('Unknown error', 'give')) +
                            '</span>'
                    );
                    $button.prop('disabled', false);
                    $buttonText.text(originalText);
                    $buttonIcon.attr('class', originalIcon);
                    $button.removeClass('loading');
                }
            },
            error: function () {
                $statusDiv.html(
                    '<span style="color: #dc3232;">' +
                        __('✗ Error disconnecting organization. Please try again.', 'give') +
                        '</span>'
                );
                $button.prop('disabled', false);
                $buttonText.text(originalText);
                $buttonIcon.attr('class', originalIcon);
                $button.removeClass('loading');
            },
        });
    }

    window.disconnectOrganization = disconnectOrganization;

    function deleteAllOrganizationData() {
        if (
            !confirm(
                __(
                    'WARNING: This will permanently delete ALL organization data from the database.\n\nThis includes:\n• Organization ID and connection status\n• Complete organization details\n• All cached data and preferences\n\nThis action CANNOT be undone. Are you sure you want to continue?',
                    'give'
                )
            )
        ) {
            return;
        }

        const $button = $('.delete-all-btn');
        const $buttonText = $button.find('.button-text');
        const $buttonIcon = $button.find('.dashicons');
        const originalText = $buttonText.text();
        const originalIcon = $buttonIcon.attr('class');

        $button.prop('disabled', true);
        $buttonText.text(__('Deleting...', 'give'));
        $buttonIcon.attr('class', 'dashicons dashicons-update');
        $button.addClass('loading');

        const $statusDiv = $(
            '<div class="delete-status" style="margin-top: 10px; padding: 8px; background: #f0f0f0; border-radius: 3px; font-size: 12px; color: #666;">' +
                __('Deleting all organization data from database...', 'give') +
                '</div>'
        );
        $button.closest('.data-actions').append($statusDiv);

        $.ajax({
            url: giveTgbSettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'giveTgbDeleteAllOrganizationData',
                nonce: giveTgbSettings.nonce,
            },
            success: function (response) {
                if (response.success) {
                    $statusDiv.html(
                        '<span style="color: #46b450;">' +
                            __('✓ All organization data deleted successfully! Refreshing page...', 'give') +
                            '</span>'
                    );
                    setTimeout(function () {
                        $(window).off('beforeunload');
                        window.location.reload();
                    }, 1500);
                } else {
                    $statusDiv.html(
                        '<span style="color: #dc3232;">' +
                            sprintf(__('✗ Error: %s', 'give'), response.data.message || __('Unknown error', 'give')) +
                            '</span>'
                    );
                    $button.prop('disabled', false);
                    $buttonText.text(originalText);
                    $buttonIcon.attr('class', originalIcon);
                    $button.removeClass('loading');
                }
            },
            error: function () {
                $statusDiv.html(
                    '<span style="color: #dc3232;">' +
                        __('✗ Error deleting data. Please try again.', 'give') +
                        '</span>'
                );
                $button.prop('disabled', false);
                $buttonText.text(originalText);
                $buttonIcon.attr('class', originalIcon);
                $button.removeClass('loading');
            },
        });
    }

    window.deleteAllOrganizationData = deleteAllOrganizationData;
})(jQuery);
