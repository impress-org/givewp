/**
 * @link https://codex.wordpress.org/Javascript_Reference/wp.media
 * @link https://wordpress.stackexchange.com/a/382291
 */

import React from 'react';
import _ from 'lodash';
import {BaseControl, Button} from '@wordpress/components';
import {upload} from '@wordpress/icons';
import {__} from '@wordpress/i18n';

export default ({label, value, onChange, help}) => {
    // The media library uses Backbone.js, which can conflict with lodash.
    _.noConflict();
    let frame;

    const openMediaLibrary = (event) => {
        event.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = window.wp.media({
            title: __('Add or upload file', 'give'),
            button: {
                text: __('Use this media', 'gie'),
            },
            multiple: false, // Set to true to allow multiple files to be selected
        });

        frame.on('select', function () {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            onChange(attachment.url);
        });

        // Finally, open the modal on click
        frame.open();
    };
    return (
        <BaseControl
            label={label}
            help={help}
        >
            <div className={'email-settings__logo-upload'}>
                <input
                    type={'url'}
                    value={value}
                    onChange={onChange}
                    className={'components-text-control__input'}
                    style={{flex: 1}}
                />
                <Button
                    className={'email-settings__logo-upload__button'}
                    icon={upload}
                    variant={'secondary'}
                    onClick={openMediaLibrary}
                >
                    {__('Add or upload file', 'give')}
                </Button>
            </div>
        </BaseControl>
    );
};
