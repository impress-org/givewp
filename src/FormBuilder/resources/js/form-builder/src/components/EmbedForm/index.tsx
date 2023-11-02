import {useCallback, useEffect, useState} from 'react';
import {createPortal} from 'react-dom';
import {useDispatch, useSelect} from '@wordpress/data';
import {store} from '@wordpress/core-data';
import {__} from '@wordpress/i18n';
import {Button, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import getWindowData from '@givewp/form-builder/common/getWindowData';

import './styles.scss';

interface EmbedFormModalProps {
    handleClose: Function;
}

interface StateProps {
    insertPostType: string;
    createPostType: string;
    newPostName: string;
    selected: string;
    isCopied: boolean;
    isInserting: boolean;
    isCreating: boolean;
    inserted: Array<number | string>;
}

/**
 * @unreleased
 */
export default function EmbedFormModal<EmbedFormModalProps>({handleClose}) {

    const {formId} = getWindowData();
    const [state, setState] = useState<StateProps>({
        insertPostType: 'page',
        createPostType: 'page',
        newPostName: '',
        selected: '',
        isCopied: false,
        isInserting: false,
        isCreating: false,
        inserted: []
    });

    const {editEntityRecord, saveEditedEntityRecord, saveEntityRecord} = useDispatch(store);

    const closeModal = useCallback(e => {
        if (e.keyCode === 27 && typeof handleClose === 'function') {
            handleClose(e);
        }
    }, []);

    useEffect(() => {
        document.addEventListener('keydown', closeModal, false);

        return () => {
            document.removeEventListener('keydown', closeModal, false);
        };
    }, []);

    const block = `<!-- wp:give/donation-form {"id":${formId}} /-->`;
    const shortcode = `[give_form id=${formId}]`;

    const postOptions = [
        {label: 'Page', value: 'page'},
        {label: 'Post', value: 'post'},
    ]

    // Get posts/pages
    const sitePages = useSelect((select) => {
        const pages = [];

        const query = {
            status: 'publish',
            per_page: -1 // do we want this?
        }

        // @ts-ignore
        const data = select(store).getEntityRecords('postType', state.insertPostType, query);

        const selectLabel = 'page' === state.insertPostType
            ? __('Select a page', 'give')
            : __('Select a post', 'give')

        pages.push({value: '', label: selectLabel, disabled: true});

        data?.forEach(page => {
            pages.push({
                value: page.id,
                label: page.title.rendered,
                content: page.content.raw,
                disabled: page.content.raw.includes(block) // disable pages that already have form block included
            })
        });

        return pages;

    }, [state.insertPostType, state.inserted]);


    const handleCopy = useCallback(() => {
        navigator.clipboard.writeText(shortcode);

        setState(prevState => {
            return {
                ...prevState,
                isCopied: true
            }
        });

        setTimeout(() => {
            setState(prevState => {
                return {
                    ...prevState,
                    isCopied: false
                }
            });
        }, 2000);
    }, []);

    const handleInsertIntoExisting = async () => {
        const content = sitePages?.find((page) => page.value == state.selected)?.content + block;

        setState(prevState => {
            return {
                ...prevState,
                isInserting: true
            }
        });

        await editEntityRecord('postType', state.insertPostType, state.selected, {content});
        await saveEditedEntityRecord('postType', state.insertPostType, state.selected, {content});

        setState(prevState => {
            return {
                ...prevState,
                isInserting: false,
                inserted: [...prevState.inserted, state.selected]
            }
        });
    }

    const handleCreateNew = async () => {
        setState(prevState => {
            return {
                ...prevState,
                isCreating: true
            }
        });

        await saveEntityRecord('postType', state.createPostType, {
            title: state.newPostName,
            content: block
        });

        setState(prevState => {
            return {
                ...prevState,
                isCreating: false
            }
        });
    }

    return createPortal(
        <div className="give-embed-modal">

            <div className="give-embed-modal-header">
                {__('Embed Form', 'give')}

                <span className="give-embed-modal-badge">
                    {__('Form ID', 'give')}: {formId}
                </span>
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
                            selected: ''
                        }
                    })}
                />

                <SelectControl
                    value={state.selected}
                    options={sitePages}
                    onChange={value => setState(prevState => {
                        return {
                            ...prevState,
                            selected: value
                        }
                    })}
                />

                {state.inserted.includes(state.selected) ? (
                    <strong>
                        {__('Form inserted!', 'give')}
                    </strong>
                ) : (
                    <Button
                        variant="secondary"
                        disabled={!state.selected || state.isInserting}
                        isBusy={state.isInserting}
                        onClick={handleInsertIntoExisting}
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
                        }
                    })}
                />

                <TextControl
                    required
                    value={state.newPostName}
                    onChange={value => setState(prevState => {
                        return {
                            ...prevState,
                            newPostName: value
                        }
                    })}
                />

                <Button
                    variant="secondary"
                    disabled={state.isCreating}
                    isBusy={state.isCreating}
                    onClick={handleCreateNew}
                >
                    {state.isCreating ? __('Creating...', 'give') : __('Create', 'give')}
                </Button>

            </div>

            <div className="give-embed-modal-row">
                <strong>
                    {__('Shortcode', 'give')}
                </strong>

                <div className="give-embed-modal-items">
                    <div>
                        <TextControl
                            readOnly
                            value={shortcode}
                            onChange={null}
                        />
                    </div>

                    <div>
                        <Button
                            variant="secondary"
                            onClick={handleCopy}
                        >
                            {state.isCopied ? __('Copied!', 'give') : __('Copy Shortcode', 'give')}
                        </Button>
                    </div>
                </div>
            </div>
        </div>,
        document.body
    )
}
