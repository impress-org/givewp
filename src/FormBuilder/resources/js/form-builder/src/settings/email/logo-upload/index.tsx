/**
 * @link https://codex.wordpress.org/Javascript_Reference/wp.media
 * @link https://wordpress.stackexchange.com/a/382291
 */

import React from 'react';
import _ from 'lodash';
import {Button, TextControl} from '@wordpress/components';
import {upload} from '@wordpress/icons';
import {__} from '@wordpress/i18n';

export default ({value, onChange}) => {
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
            title: __('Add or upload file', 'givewp'),
            button: {
                text: __('Use this media', 'givewp'),
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
        <div style={{display: 'flex', flexDirection: 'column', marginBottom: '8px', width: '100%'}}>
            <div>
                {' '}
                {/* Wrapping the TextControl solves a spacing issue */}
                <TextControl type={'url'} label={__('Logo URL', 'givewp')} value={value} onChange={onChange} />
            </div>
            <Button
                icon={upload}
                variant={'secondary'}
                onClick={openMediaLibrary}
                style={{width: '100%', justifyContent: 'center', marginBottom: '8px'}}
            >
                {__('Add or upload file', 'givewp')}
            </Button>
        </div>
    );
};
