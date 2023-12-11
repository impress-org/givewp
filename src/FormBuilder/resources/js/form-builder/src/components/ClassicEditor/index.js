/**
 * This component uses the WP Editor API, which makes it possible to dynamically
 * instantiate the editor from JS. There are two parts to it:
 *
 * 1 - All editor related scripts and stylesheets have to be enqueued from PHP by using wp_enqueue_editor()
 * 2 - Initialization is left for the script that is adding the editor instance. It requires the textarea that will become the Text editor tab to be already created and not hidden in the DOM. Filtering of the settings is done on adding the editor instance from JS.
 *
 * @see https://make.wordpress.org/core/2017/05/20/editor-api-changes-in-4-8/
 *
 * This component is an adaptation of the Classic block (think of this block as a boilerplate for this file) from Gutenberg which is shipped with the WP core.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/packages/block-library/src/freeform/edit.js
 */
import {debounce} from '@wordpress/compose';
import {useEffect, useRef, useState} from '@wordpress/element';
import {BACKSPACE, DELETE, F10, isKeyboardEvent} from '@wordpress/keycodes';
import {BaseControl} from '@wordpress/components';

const {wp} = window;

function isTmceEmpty(editor) {
    // When tinyMce is empty the content seems to be:
    // <p><br data-mce-bogus="1"></p>
    // avoid expensive checks for large documents
    const body = editor.getBody();
    if (body.childNodes.length > 1) {
        return false;
    } else if (body.childNodes.length === 0) {
        return true;
    }
    if (body.childNodes[0].childNodes.length > 1) {
        return false;
    }
    return /^\n?$/.test(body.innerText || body.textContent);
}

/**
 * @since 3.2.0
 */
export default function ClassicEditor({id, label = null, content, setContent, rows = 20}) {
    const didMount = useRef(false);

    const [editorContent, setEditorContent] = useState(content);

    useEffect(() => {
        if (!didMount.current) {
            return;
        }

        setContent(editorContent);
    }, [editorContent]);

    useEffect(() => {
        if (!didMount.current || editorContent === content) {
            return;
        }

        const editor = window.tinymce.get(`editor-${id}`);

        editor.setContent(content);
    }, [content]);

    useEffect(() => {
        didMount.current = true;

        function onSetup(editor) {
            let bookmark;
            if (editorContent) {
                editor.on('loadContent', () => {
                    if (!!editor._lastChange && editor._lastChange !== editorContent) {
                        editor.setContent(editor.getContent());
                    } else {
                        editor.setContent(editorContent);
                    }
                });
            }

            editor.on('blur', () => {
                bookmark = editor.selection.getBookmark(2, true);
                // There is an issue with Chrome and the editor.focus call in core at https://core.trac.wordpress.org/browser/trunk/src/js/_enqueues/lib/link.js#L451.
                // This causes a scroll to the top of editor content on return from some content updating dialogs so tracking
                // scroll position until this is fixed in core.
                const scrollContainer = document.querySelector('.interface-interface-skeleton__content');
                const scrollPosition = scrollContainer.scrollTop;

                editor.once('focus', () => {
                    if (bookmark) {
                        editor.selection.moveToBookmark(bookmark);
                        if (scrollContainer.scrollTop !== scrollPosition) {
                            scrollContainer.scrollTop = scrollPosition;
                        }
                    }
                });

                return false;
            });

            editor.on('mousedown touchstart', () => {
                bookmark = null;
            });

            const debouncedOnChange = debounce(() => {
                const newContent = editor.getContent();

                if (newContent !== editor._lastChange) {
                    editor._lastChange = newContent;
                    setEditorContent(newContent);
                }
            }, 250);
            editor.on('Paste Change input Undo Redo', debouncedOnChange);

            // We need to cancel the debounce call because when we remove
            // the editor (onUnmount) this callback is executed in
            // another tick. This results in setting the content to empty.
            editor.on('remove', debouncedOnChange.cancel);

            editor.on('keydown', (event) => {
                if (isKeyboardEvent.primary(event, 'z')) {
                    // Prevent the gutenberg undo kicking in so TinyMCE undo stack works as expected.
                    event.stopPropagation();
                }

                if ((event.keyCode === BACKSPACE || event.keyCode === DELETE) && isTmceEmpty(editor)) {
                    // Delete the block.
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }

                const {altKey} = event;
                /*
                 * Prevent Mousetrap from kicking in: TinyMCE already uses its own
                 * `alt+f10` shortcut to focus its toolbar.
                 */
                if (altKey && event.keyCode === F10) {
                    event.stopPropagation();
                }
            });

            editor.on('init', () => {
                const rootNode = editor.getBody();

                // Create the toolbar by refocussing the editor.
                if (rootNode.ownerDocument.activeElement === rootNode) {
                    rootNode.blur();
                    editor.focus();
                }
            });
        }

        /**
         * For references about how to set the TinyMCE toolbar and plugins, check the
         * wp_tinymce_inline_scripts() on the wp-includes/script-loader.php file.
         *
         * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/script-loader.php#L512C39-L646
         */
        function initialize() {
            setTimeout(() => {
                wp.editor.initialize(`editor-${id}`, {
                    mediaButtons: false,
                    tinymce: {
                        tinymce: true,
                        plugins:
                            'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview',
                        toolbar1:
                            'bold,italic,wp_add_media,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,fullscreen,wp_adv',
                        toolbar2:
                            'formatselect,strikethrough,hr,forecolor,pastetext,removeformat,unlink,outdent,indent,undo,redo',
                        setup: onSetup,
                        resize: false,
                        statusbar: false,
                    },
                    quicktags: true,
                });
            }, 250);
        }

        function onReadyStateChange() {
            if (document.readyState === 'complete') {
                initialize();
            }
        }

        if (document.readyState === 'complete') {
            initialize();
        } else {
            document.addEventListener('readystatechange', onReadyStateChange);
        }

        return () => {
            document.removeEventListener('readystatechange', onReadyStateChange);
            wp.editor.remove(`editor-${id}`);
        };
    }, []);

    function focus() {
        const editor = window.tinymce.get(`editor-${id}`);
        if (editor) {
            editor.focus();
        }
    }

    function onToolbarKeyDown(event) {
        // Prevent WritingFlow from kicking in and allow arrows navigation on the toolbar.
        event.stopPropagation();
        // Prevent Mousetrap from moving focus to the top toolbar when pressing `alt+f10` on this block toolbar.
        event.nativeEvent.stopImmediatePropagation();
    }

    return (
        <div className={'givewp-classic-editor'}>
            <BaseControl id={`editor-base-control-${id}`} label={label}>
                <textarea
                    rows={rows}
                    id={`editor-${id}`}
                    onChange={(event) => {
                        const editor = window.tinymce.get(`editor-${id}`);
                        editor._lastChange = event.target.value;
                        setEditorContent(event.target.value);
                    }}
                />
            </BaseControl>
        </div>
    );
}
