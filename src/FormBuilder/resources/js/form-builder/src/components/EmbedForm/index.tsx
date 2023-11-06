import {MouseEventHandler, useCallback, useEffect, useRef, useState} from 'react';
import {createPortal} from 'react-dom';
import cx from 'classnames';
import {useDispatch, useSelect} from '@wordpress/data';
import {store} from '@wordpress/core-data';
import {__, sprintf} from '@wordpress/i18n';
import {Button, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import {external} from '@wordpress/icons';
import getWindowData from '@givewp/form-builder/common/getWindowData';
import {CopyIcon, ExitIcon} from '@givewp/components/AdminUI/Icons';
import {Interweave} from 'interweave';

import './styles.scss';

interface EmbedFormModalProps {
    handleClose: MouseEventHandler<HTMLButtonElement>;
}

type Post = {
    value: string,
    label: string,
    content: string,
    disabled: boolean
}

interface StateProps {
    posts: Array<Post>;
    insertPostType: string;
    createPostType: string;
    currentPostType: string;
    newPostName: string;
    selectedPost: string;
    selectedStyle: string;
    isCopied: boolean;
    isInserting: boolean;
    isCreating: boolean;
    isInserted: boolean;
    isCreated: boolean;
    createdLink: string;
    insertedLink: string;
}

/**
 * @unreleased
 */
export default function EmbedFormModal({handleClose}: EmbedFormModalProps) {

    const newPostNameRef = useRef<HTMLInputElement>(null);
    const {formId} = getWindowData();
    const [state, setState] = useState<StateProps>({
        posts: [],
        insertPostType: 'page',
        createPostType: 'page',
        currentPostType: 'page',
        newPostName: '',
        selectedPost: '',
        selectedStyle: 'full',
        isCopied: false,
        isInserting: false,
        isCreating: false,
        isInserted: false,
        isCreated: false,
        createdLink: null,
        insertedLink: null,
    });

    const {editEntityRecord, saveEditedEntityRecord, saveEntityRecord} = useDispatch(store);

    const closeModal = useCallback((e) => {
        if (e.keyCode === 27 && typeof handleClose === 'function') {
            handleClose(e);
        }
    }, []);

    useEffect(() => {
        document.addEventListener('keydown', closeModal, false);

        return () => document.removeEventListener('keydown', closeModal, false);
    }, []);

    const block = `<!-- wp:give/donation-form {"id":${formId}} /-->`;
    const shortcode = `[give_form id=${formId}]`;

    const postOptions = [
        {label: 'Page', value: 'page'},
        {label: 'Post', value: 'post'},
    ];

    const displayStyles = [
        {
            label: __('Full form', 'give'),
            value: 'full',
            description: __('All fields are visible on one page with the donate button at the bottom', 'give'),
        },
        {
            label: __('Modal', 'give'),
            value: 'modal',
            description: __('Add description for Modal', 'give'),
        },
        {
            label: __('New Tab', 'give'),
            value: 'new-tab',
            description: __('Add description for New tab', 'give'),
        },
    ];

    // Fetch posts/pages
    const isLoadingPages = useSelect((select) => {
        const filtered = [];
        const query = {
            status: ['publish', 'draft'],
            per_page: -1, // do we want this?
        };
        // @ts-ignore
        const data = select(store).getEntityRecords('postType', state.currentPostType, query);

        if (data) {
            data?.forEach(page => {
                filtered.push({
                    value: page.id,
                    label: page.title.rendered,
                    content: page.content.raw,
                    disabled: page.content.raw.includes(block), // disable pages that already have form block included
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

    /**
     * Get site posts/pages for select option
     */
    const getPostsList = useCallback(() => {
        const pages = [];

        if (isLoadingPages) {
            pages.push({value: '', label: __('Loading...', 'give'), disabled: true});
        } else {
            const selectLabel = 'page' === state.insertPostType
                ? __('Select a page', 'give')
                : __('Select a post', 'give');

            pages.push({value: '', label: selectLabel, disabled: true});
        }

        if (state.posts[state.insertPostType]) {
            pages.push(...state.posts[state.insertPostType]);
        }

        return pages;
    }, [state.posts, isLoadingPages]);

    const getStyleDescription = () => displayStyles.find(style => style.value === state.selectedStyle).description;

    /**
     * Handle copying shortcode to clipboard
     */
    const handleCopy = useCallback(async () => {
        await navigator.clipboard.writeText(shortcode);

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
    }, []);

    /**
     * Handle inserting form into existing post/page
     */
    const handleInsert = async () => {
        setState(prevState => {
            return {
                ...prevState,
                isInserting: true,
            };
        });

        const content = state?.posts[state.insertPostType]?.find((page) => page.value == state.selectedPost)?.content + block;

        await editEntityRecord('postType', state.insertPostType, state.selectedPost, {content});
        const response = await saveEditedEntityRecord('postType', state.insertPostType, state.selectedPost, {content});

        setState(prevState => {
            return {
                ...prevState,
                isInserting: false,
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

        if (state.isCreating) {
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
            content: block,
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
                    onChange={value => setState(prevState => {
                        return {
                            ...prevState,
                            selectedPost: value,
                        };
                    })}
                />

                {state.isInserted ? (
                    <div className="give-embed-modal-items">
                        <div>
                            <Button
                                href={state.insertedLink}
                                target="_blank"
                                icon={external}
                                variant="secondary"
                            >
                                {sprintf(
                                    __('View inserted %s', 'give'),
                                    'page' === state.insertPostType
                                        ? __('Page', 'give')
                                        : __('Post', 'give'))}
                            </Button>
                        </div>
                        <div>
                            <Button
                                variant="tertiary"
                                onClick={() => setState(prevState => {
                                    return {
                                        ...prevState,
                                        isInserted: false,
                                        selectedPost: '',
                                        insertedLink: null,
                                    };
                                })}
                            >
                                {__('Add another', 'give')}
                            </Button>
                        </div>
                    </div>
                ) : (
                    <Button
                        variant="secondary"
                        disabled={!state.selectedPost || state.isInserting}
                        isBusy={state.isInserting}
                        onClick={handleInsert}
                    >
                        {state.isInserting ? __('Inserting form...', 'give') : __('Insert form', 'give')}
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
                    className={cx({'give-embed-modal-input-error': isPageAlreadyCreated})}
                    help={isPageAlreadyCreated
                        ? sprintf(
                            __('%s with that name already exists', 'give'),
                            'page' === state.currentPostType
                                ? __('Page', 'give')
                                : __('Post', 'give'))
                        : null
                    }
                    onChange={value => setState(prevState => {
                        return {
                            ...prevState,
                            newPostName: value,
                        };
                    })}
                />

                {state.isCreated ? (
                    <div className="give-embed-modal-items">
                        <div>
                            <Button
                                href={state.createdLink}
                                target="_blank"
                                icon={external}
                                variant="secondary"
                            >
                                {sprintf(
                                    __('View created %s', 'give'),
                                    'page' === state.currentPostType
                                        ? __('Page', 'give')
                                        : __('Post', 'give'))}
                            </Button>
                        </div>
                        <div>
                            <Button
                                variant="tertiary"
                                onClick={() => {
                                    newPostNameRef.current?.focus();

                                    setState(prevState => {
                                        return {
                                            ...prevState,
                                            isCreated: false,
                                            newPostName: '',
                                            createdLink: null,
                                        };
                                    });
                                }}
                            >
                                {__('Add another', 'give')}
                            </Button>
                        </div>
                    </div>
                ) : (
                    <>
                        {!isPageAlreadyCreated && (
                            <Button
                                variant="secondary"
                                disabled={state.isCreating}
                                isBusy={state.isCreating}
                                onClick={handleCreateNew}
                            >
                                {state.isCreating ? __('Creating...', 'give') : __('Create', 'give')}
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
                            icon={CopyIcon}
                            variant="secondary"
                            onClick={handleCopy}
                        >
                            {state.isCopied ? __('Copied!', 'give') : __('Copy Shortcode', 'give')}
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
