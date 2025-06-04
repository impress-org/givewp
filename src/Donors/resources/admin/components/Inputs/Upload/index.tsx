/**
 * @link https://codex.wordpress.org/Javascript_Reference/wp.media
 * @link https://wordpress.stackexchange.com/a/382291
 */

import _ from 'lodash';
import {__, sprintf} from '@wordpress/i18n';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
type MediaLibrary = {
    id: string;
    value: string;
    onChange: (id: string, url: string, alt: string) => void;
    reset: () => void;
    label: string;
};

/**
 * @unreleased
 */
export default function UploadMedia({id, value, onChange, label, reset}: MediaLibrary) {
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
            title: __('Upload Media', 'give'),
            button: {
                text: __('Use this media', 'gie'),
            },
            library: {
                type: 'image', // Restricts media library to image files only
            },
            multiple: false, // Set to true to allow multiple files to be selected
        });

        frame.on('select', function () {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            if (!attachment.type || attachment.type !== 'image') {
                alert(__('Please select an image file only.', 'give'));
                frame.open();
                return;
            }

            onChange(attachment.id, attachment.url, attachment.alt);
        });

        // Finally, open the modal on click
        frame.open();
    };

    const resetImage = () => {
        reset();
    };

    return (
        <div id={id} className={styles.controlWrapper}>
            <div className={styles.control}>
                {value ? (
                    <img
                        className={styles.image}
                        src={value}
                        alt={__('uploaded image', 'give')}
                    />
                ) : (
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10 9C11.6569 9 13 7.65685 13 6C13 4.34315 11.6569 3 10 3C8.34315 3 7 4.34315 7 6C7 7.65685 8.34315 9 10 9Z"
                            stroke="#6B7280"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        />
                        <path
                            d="M3 18C3 14.134 6.13401 11 10 11C13.866 11 17 14.134 17 18"
                            stroke="#6B7280"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        />
                    </svg>
                )}
            </div>

            <div className={styles.options}>
                <button
                    className={styles.update}
                    onClick={openMediaLibrary}
                >
                    {sprintf(value ? __('Change %s', 'give') : __('Upload %s', 'give'), label.toLowerCase())}
                </button>
                {value && (
                    <button
                        className={styles.remove}
                        onClick={resetImage}
                    >
                        {sprintf(__('Remove %s', 'give'), label.toLowerCase())}
                    </button>
                )}
            </div>
        </div>
    );
}
