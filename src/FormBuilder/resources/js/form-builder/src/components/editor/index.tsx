import {useEffect, useRef} from '@wordpress/element';
import {__} from '@wordpress/i18n';

import ReactQuill from 'react-quill';
import _ from 'lodash';
import cx from 'classnames';

import 'react-quill/dist/quill.snow.css';

interface EditorProps {
    onChange: (value: string) => void;
    value: string;
    className?: string;
}

/**
 * A simple WYSIWYG editor for use in basic settings.
 *
 * @see https://github.com/zenoamaro/react-quill
 */
export default function Editor ({onChange, value, className}: EditorProps) {
    // The media library uses Backbone.js, which can conflict with lodash.
    _.noConflict();
    let frame;

    const editorRef = useRef();

    const openMediaLibrary = (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (frame) {
            frame.open();
            return;
        }

        frame = window.wp.media({
            title: __('Add or upload file', 'give'),
            button: {
                text: __('Use this media', 'give'),
            },
            multiple: false, // Set to true to allow multiple files to be selected
        });

        frame.on('select', function () {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            // @ts-ignore
            const editor = editorRef.current.getEditor();
            const cursorPosition = editor.getSelection()?.index ?? 0;
            editor.insertEmbed(cursorPosition, 'image', attachment.url);
            editor.setSelection(cursorPosition + 1);
        });

        // Finally, open the modal on click
        frame.open();
    };

    useEffect(() => {
        const mediaToolbarButton = document.querySelector('.ql-wpmedia');
        if (mediaToolbarButton) {
            mediaToolbarButton.addEventListener('click', openMediaLibrary);
            return () => {
                mediaToolbarButton.removeEventListener('click', openMediaLibrary);
            };
        }
    }, []);

    const modules = {
        toolbar: {
            container: '#toolbar',
        },
    };

    const formats = [
        'header',
        'bold',
        'italic',
        'underline',
        'strike',
        'blockquote',
        'list',
        'bullet',
        'indent',
        'link',
        'image',
    ];

    const baseClasses = cx('text-editor givewp-ql-text-editor', className);

    return (
        <div className={baseClasses}>
            <CustomToolbar>
                <button id="ql-wpmedia" className="ql-wpmedia" onClick={openMediaLibrary}>
                    <MediaIcon />
                </button>
            </CustomToolbar>

            <ReactQuill
                style={{height: '16rem', borderRadius: '2px'}}
                ref={editorRef}
                theme="snow"
                value={value}
                onChange={onChange}
                modules={modules}
                formats={formats}
                bounds=".givewp-ql-text-editor"
            />
        </div>
    );
};

const CustomToolbar = ({children}) => {
    return (
        <div id="toolbar">
            <select className="ql-header" defaultValue={''} onChange={(e) => e.persist()}>
                <option value="1"></option>
                <option value="2"></option>
                <option selected></option>
            </select>
            <button className="ql-bold"></button>
            <button className="ql-italic"></button>
            <button className="ql-blockquote"></button>
            <button className="ql-underline"></button>
            <button className="ql-link"></button>

            {children}
        </div>
    );
};

const MediaIcon = () => {
    return (
        <svg viewBox="0 0 18 18">
            <rect className="ql-stroke" height="10" width="12" x="3" y="4"></rect>
            <circle className="ql-fill" cx="6" cy="7" r="1"></circle>
            <polyline className="ql-even ql-fill" points="5 12 5 11 7 9 8 10 11 7 13 9 13 12 5 12"></polyline>
        </svg>
    );
};
