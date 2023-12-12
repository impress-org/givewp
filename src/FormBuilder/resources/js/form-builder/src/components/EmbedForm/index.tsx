import {MouseEventHandler, useCallback, useEffect, useRef, useState} from 'react';
import cx from 'classnames';
import {createPortal} from 'react-dom';
import {useDispatch, useSelect} from '@wordpress/data';
import {store} from '@wordpress/core-data';
import {__, sprintf} from '@wordpress/i18n';
import {Button, RadioControl, SelectControl, Spinner, TextControl} from '@wordpress/components';
import {external} from '@wordpress/icons';
import getWindowData from '@givewp/form-builder/common/getWindowData';
import {CheckIcon} from '@givewp/form-builder/components/icons';
import {CopyIcon, ExitIcon} from '@givewp/components/AdminUI/Icons';
import {Interweave} from 'interweave';

import './styles.scss';

interface EmbedFormModalProps {
    handleClose: MouseEventHandler<HTMLButtonElement>;
}

type Post = {
    value: string,
    label: string,
    content: string
}

interface StateProps {
    posts: Array<Post>;
    insertPostType: string;
    createPostType: string;
    currentPostType: string;
    newPostName: string;
    selectedPost: string;
    selectedStyle: string;
    openFormButton: string;
    isCopied: boolean;
    isInserting: boolean;
    insertPageNotSelected: boolean;
    isCreating: boolean;
    isInserted: boolean;
    isCreated: boolean;
    createdLink: string;
    insertedLink: string;
}

/**
 * @since 3.2.0
 */
export default function EmbedFormModal({handleClose}: EmbedFormModalProps) {

    const {formId} = getWindowData();
    const newPostNameRef = useRef<HTMLInputElement>(null);
    const openFormBtnRef = useRef<HTMLInputElement>(null);
    const viewInsertedPageBtnRef = useRef<HTMLButtonElement>(null);
    const viewCreatedPageBtnRef = useRef<HTMLButtonElement>(null);

    const [state, setState] = useState<StateProps>({
        posts: [],
        insertPostType: 'page',
        createPostType: 'page',
        currentPostType: 'page',
        newPostName: '',
        selectedPost: '',
        selectedStyle: 'onpage',
        openFormButton: '',
        isCopied: false,
        isInserting: false,
        insertPageNotSelected: false,
        isCreating: false,
        isInserted: false,
        isCreated: false,
        createdLink: null,
        insertedLink: null,
    });

    const {editEntityRecord, saveEditedEntityRecord, saveEntityRecord} = useDispatch(store);

    const closeModal = useCallback(e => {
        if (e.keyCode === 27) {
            handleClose(e);
        }
    }, []);

    useEffect(() => {
        document.addEventListener('keydown', closeModal, false);
        return () => document.removeEventListener('keydown', closeModal, false);
    }, []);

    const postOptions = [
        {label: 'Page', value: 'page'},
        {label: 'Post', value: 'post'},
    ];

    const displayStyles = [
        {
            label: __('Full form', 'give'),
            value: 'onpage',
            description: __('All fields are visible on one page with the donate button at the bottom', 'give'),
        },
        {
            label: __('Modal', 'give'),
            value: 'modal',
            description: __('Only a button is visible; clicking it opens the form in a modal window', 'give'),
        },
        {
            label: __('New Tab', 'give'),
            value: 'newTab',
            description: __('Only a button is visible; clicking it opens the form in a new window', 'give'),
        },
    ];

    // Fetch posts/pages
    const isLoadingPages = useSelect((select) => {
        const filtered: Array<Post> = [];
        // @ts-ignore
        const data = select(store).getEntityRecords('postType', state.currentPostType, {
            status: ['publish', 'draft'],
            per_page: -1, // do we want this?
        });

        if (data) {
            data?.forEach(page => {
                filtered.push({
                    value: page.id,
                    label: page.title.raw || __('(no title)', 'give'),
                    content: page.content.raw
                });
            });

            // Adding this to state so that we have both posts and pages available
            // This is needed for post/page exist check
            setState(prevState => {
                return {
                    ...prevState,
                    isInserting: false,
                    posts: {
                        ...prevState.posts,
                        [state.currentPostType]: filtered,
                    },
                };
            });
        }

        return !data;

    }, [state.createPostType, state.insertPostType]);

    /**
     * Check if page is already created
     * Works for posts and pages
     */
    const isPageAlreadyCreated = !state.isCreated && state.newPostName
        && state.posts[state.createPostType]?.filter(post => post.label == state.newPostName).length > 0;

    const isButton = ['newTab', 'modal'].includes(state.selectedStyle);

    /**
     * Get site posts/pages for select option
     */
    const getPostsList = useCallback(() => {
        const pages = [];

        if (isLoadingPages) {
            pages.push({value: '', label: __('Loading...', 'give'), disabled: true});
        } else {
            const label = 'page' === state.insertPostType
                ? __('Select a page', 'give')
                : __('Select a post', 'give');

            pages.push({value: '', label, disabled: true});
        }

        if (state.posts[state.insertPostType]) {
            pages.push(...state.posts[state.insertPostType]);
        }

        return pages;
    }, [state.posts, isLoadingPages]);

    const getStyleDescription = () => displayStyles.find(style => style.value === state.selectedStyle).description;

    const getBlockComment = () => {

        const attributes = {
            id: formId
        }

        if (isButton) {
            attributes['displayStyle'] = state.selectedStyle;
            attributes['continueButtonTitle'] = state.openFormButton;
        }

        return `<!-- wp:give/donation-form ${JSON.stringify(attributes)} /-->`;
    }

    const getShortcode = () => {
        const shortcodeAttributes = [];

        const attributes = {
            id: formId
        }

        if (isButton) {
            attributes['display_style'] = state.selectedStyle;
            attributes['continue_button_title'] = state.openFormButton;
        }

        for (const key in attributes) {
            shortcodeAttributes.push(`${key}="${attributes[key]}"`);
        }

        return `[give_form ${shortcodeAttributes.join(' ')}]`;
    }

    /**
     * Handle copying shortcode to clipboard
     */
    const handleCopy = async () => {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(getShortcode());
        } else {
            const textArea = document.createElement('textarea');

            textArea.value = getShortcode();
            textArea.style.display = 'hidden';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (error) {
                console.error(error);
            } finally {
                textArea.remove();
            }
        }

        setState(prevState => {
            return {
                ...prevState,
                isCopied: true,
            };
        });

        setTimeout(() => {
            setState(prevState => {
                return {
                    ...prevState,
                    isCopied: false,
                };
            });
        }, 2000);
    }

    /**
     * Handle inserting form into existing post/page
     */
    const handleInsert = async () => {

        if (isButton && !state.openFormButton) {
            openFormBtnRef.current?.focus();
            return;
        }

        if (!state.selectedPost) {
            setState(prevState => {
                return {
                    ...prevState,
                    insertPageNotSelected: true,
                };
            });

            return;
        }

        setState(prevState => {
            return {
                ...prevState,
                isInserting: true,
            };
        });

        const content = state?.posts[state.insertPostType]?.find((page) => page.value == state.selectedPost)?.content + getBlockComment();

        await editEntityRecord('postType', state.insertPostType, state.selectedPost, {content});
        const response = await saveEditedEntityRecord('postType', state.insertPostType, state.selectedPost, {content});

        setState(prevState => {
            return {
                ...prevState,
                isInserting: false,
                insertPageNotSelected: false,
                isInserted: true,
                insertedLink: response?.link,
            };
        });
    };

    /**
     * Handle creating a new post/page
     */
    const handleCreateNew = async () => {
        if (!state.newPostName) {
            newPostNameRef.current?.focus();
            return;
        }

        if (isButton && !state.openFormButton) {
            openFormBtnRef.current?.focus();
            return;
        }

        setState(prevState => {
            return {
                ...prevState,
                isCreating: true,
            };
        });

        const response = await saveEntityRecord('postType', state.createPostType, {
            title: state.newPostName,
            content: getBlockComment(),
        });

        setState(prevState => {
            return {
                ...prevState,
                isCreating: false,
                isCreated: true,
                createdLink: response?.link,
            };
        });
    };

    const getContentType = type => {
        return type === 'page'
            ? __('page', 'give')
            : __('post', 'give');
    }

    return createPortal(
        <div className="give-embed-modal">

            <div className="give-embed-modal-header">
                {__('Embed Form', 'give')}

                <span className="give-embed-modal-badge">
                    {__('Form ID', 'give')}: {formId}
                </span>

                <button
                    aria-label={__('Close Embed Form modal window', 'give')}
                    onClick={handleClose}
                >
                    <ExitIcon />
                </button>
            </div>

            <div className="give-embed-modal-row">

                <strong>
                    {__('Form settings', 'give')}
                </strong>

                <SelectControl
                    label={__('Display style', 'give')}
                    value={state.selectedStyle}
                    options={displayStyles}
                    onChange={value => setState(prevState => {
                        return {
                            ...prevState,
                            selectedStyle: value,
                        };
                    })}
                    help={getStyleDescription()}
                />

                {isButton && (
                    <TextControl
                        ref={openFormBtnRef}
                        placeholder={__('Donate', 'give')}
                        label={__('Button label', 'give')}
                        value={state.openFormButton}
                        onChange={value => setState(prevState => {
                            return {
                                ...prevState,
                                openFormButton: value,
                            };
                        })}
                    />
                )}

            </div>

            <div className="give-embed-modal-row">

                <strong>
                    {__('Add to existing content', 'give')}
                </strong>

                <RadioControl
                    className="give-embed-modal-radio"
                    selected={state.insertPostType}
                    options={postOptions}
                    onChange={value => setState(prevState => {
                        return {
                            ...prevState,
                            insertPostType: value,
                            currentPostType: value,
                            selectedPost: '',
                            isInserted: false,
                        };
                    })}
                />

                <SelectControl
                    value={state.selectedPost}
                    options={getPostsList()}
                    disabled={state.isInserted}
                    help={state.insertPageNotSelected
                        ? <p className="give-embed-modal-select-error">
                            {sprintf(
                                __('Please select a %s', 'give'),
                                getContentType(state.insertPostType)
                            )}
                        </p>
                        : null
                    }
                    onChange={value => setState(prevState => {
                        return {
                            ...prevState,
                            insertPageNotSelected: false,
                            selectedPost: value,
                        };
                    })}
                />

                {state.isInserted ? (
                    <div className="give-embed-modal-items">
                        <div>
                            <Button
                                icon={CheckIcon}
                                variant="secondary"
                                onClick={() => viewInsertedPageBtnRef.current?.focus()}
                            >
                                {__('Form inserted', 'give')}
                            </Button>
                        </div>
                        <div>
                            <Button
                                ref={viewInsertedPageBtnRef}
                                href={state.insertedLink}
                                target="_blank"
                                icon={external}
                                variant="tertiary"
                            >
                                {sprintf(
                                    __('View %s', 'give'),
                                    getContentType(state.insertPostType)
                                )}
                            </Button>
                        </div>
                    </div>
                ) : (
                    <Button
                        variant="secondary"
                        onClick={handleInsert}
                    >
                        {state.isInserting && <Spinner />}
                        {state.isInserting
                            ? __('Inserting form', 'give')
                            : __('Insert form', 'give')}
                    </Button>
                )}

            </div>

            <div className="give-embed-modal-row">

                <strong>
                    {__('Create new', 'give')}
                </strong>

                <RadioControl
                    className="give-embed-modal-radio"
                    selected={state.createPostType}
                    options={postOptions}
                    onChange={value => setState(prevState => {
                        return {
                            ...prevState,
                            createPostType: value,
                            currentPostType: value,
                            isCreated: false,
                        };
                    })}
                />

                <TextControl
                    readOnly={state.isCreated}
                    ref={newPostNameRef}
                    value={state.newPostName}
                    onChange={value => setState(prevState => {
                        return {
                            ...prevState,
                            newPostName: value,
                        };
                    })}
                    className={cx({'give-embed-modal-input-error': isPageAlreadyCreated})}
                    help={isPageAlreadyCreated
                        ? <p className="give-embed-modal-select-error">
                            {sprintf(
                                __('%s with that name already exists', 'give'),
                                getContentType(state.createPostType))}
                        </p>
                        : null
                    }
                />

                {state.isCreated ? (
                    <div className="give-embed-modal-items">
                        <div>
                            <Button
                                icon={CheckIcon}
                                variant="secondary"
                                onClick={() => viewCreatedPageBtnRef.current?.focus()}
                            >
                                {__('Created page', 'give')}
                            </Button>
                        </div>
                        <div>
                            <Button
                                ref={viewCreatedPageBtnRef}
                                href={state.createdLink}
                                target="_blank"
                                icon={external}
                                variant="tertiary"
                            >
                                {sprintf(
                                    __('View %s', 'give'),
                                    getContentType(state.createPostType)
                                )}
                            </Button>
                        </div>
                    </div>
                ) : (
                    <>
                        {!isPageAlreadyCreated && (
                            <Button
                                variant="secondary"
                                onClick={handleCreateNew}
                            >
                                {state.isCreating && <Spinner />}
                                {state.isCreating
                                    ? sprintf(
                                        __('Creating %s', 'give'),
                                        getContentType(state.createPostType)
                                    )
                                    : __('Create', 'give')}
                            </Button>
                        )}
                    </>
                )}

            </div>

            <div className="give-embed-modal-row">
                <strong>
                    {__('Not using the block editor?', 'give')}
                </strong>

                <div className="give-embed-modal-helptext">
                    {__('Copy and paste the shortcode within your page builder.', 'give')}
                </div>

                <div className="give-embed-modal-items give-embed-modal-copy">
                    <div>
                        <Button
                            icon={state.isCopied ? CheckIcon : CopyIcon}
                            variant="secondary"
                            onClick={handleCopy}
                        >
                            {state.isCopied ? __('Copied', 'give') : __('Copy Shortcode', 'give')}
                        </Button>
                    </div>
                    <div>
                        <Interweave
                            content={sprintf(
                                __('%s about the shortcode', 'give'),
                                `<a href="https://givewp.com/documentation/core/shortcodes/" target="_blank">${__('Learn more', 'give')}</a>`,
                            )}
                        />
                    </div>
                </div>
            </div>
        </div>,
        document.body,
    );
}
