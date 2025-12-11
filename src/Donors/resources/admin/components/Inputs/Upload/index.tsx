/**
 * @link https://codex.wordpress.org/Javascript_Reference/wp.media
 * @link https://wordpress.stackexchange.com/a/382291
 */

/**
 * External Dependencies
 */
import _ from 'lodash';

/**
 * WordPress Dependencies
 */
import {__, sprintf} from '@wordpress/i18n';

/**
 * Internal Dependencies
 */
import { ProfileIcon } from '@givewp/components/AdminDetailsPage/Icons';
import styles from './styles.module.scss';

/**
 * @since 4.4.0
 */
type MediaLibrary = {
    id: string;
    value: string;
    onChange: (id: string, url: string, alt: string) => void;
    reset: () => void;
    label: string;
};

/**
 * @since 4.4.0
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
                    <ProfileIcon />
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
