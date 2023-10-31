import {useCallback, useState} from 'react';
import {useDispatch, useSelect} from '@wordpress/data';
import {store} from '@wordpress/core-data';
import {__} from '@wordpress/i18n';
import {Button, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import getWindowData from '@givewp/form-builder/common/getWindowData';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';

import './styles.scss';

interface EmbedFormModalProps {
    handleClose: Function;
}

interface StateProps {
    postType: string;
    selected: string;
    isCopied: boolean;
    isInserting: boolean;
    inserted: Array<number | string>;
}

/**
 * @unreleased
 */
export default function EmbedFormModal<EmbedFormModalProps>({handleClose}) {

    const {formId} = getWindowData();
    const [state, setState] = useState<StateProps>({
        postType: 'page',
        selected: '',
        isCopied: false,
        isInserting: false,
        inserted: []
    });

    const {editEntityRecord, saveEditedEntityRecord} = useDispatch(store);

    const shortcode = `[give_form id=${formId}]`;

    // Get posts/pages
    const sitePages = useSelect((select) => {
        const pages = [];

        const query = {
            status: 'publish',
            per_page: -1 // do we want this?
        }

        // @ts-ignore
        const data = select(store).getEntityRecords('postType', state.postType, query);

        const selectLabel = 'page' === state.postType
            ? __('Select a page', 'give')
            : __('Select a post', 'give')

        pages.push({value: '', label: selectLabel, disabled: true});

        data?.forEach(page => {
            pages.push({
                value: page.id,
                label: page.title.rendered,
                content: page.content.raw,
                disabled: page.content.raw.includes(shortcode) // disable pages that already have shortcode in content
            })
        });

        return pages;

    }, [state.postType, state.inserted]);


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

    const handleInsert = async () => {
        const content = sitePages?.find((page) => page.value == state.selected)?.content + `<!-- wp:shortcode -->${shortcode}<!-- /wp:shortcode -->`;

        setState(prevState => {
            return {
                ...prevState,
                isInserting: true
            }
        });

        await editEntityRecord('postType', state.postType, state.selected, {content});
        await saveEditedEntityRecord('postType', state.postType, state.selected, {content});

        setState(prevState => {
            return {
                ...prevState,
                isInserting: false,
                inserted: [...prevState.inserted, state.selected]
            }
        });
    }

    return (
        <ModalDialog
            title={__('Embed Form', 'give')}
            handleClose={handleClose}
        >
            <div className="give-embed-modal">

                <div className="give-embed-modal-row give-embed-modal-divider">
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

                <div className="give-embed-modal-row give-embed-modal-row-radio">

                    <strong>
                        {__('Add to existing content', 'give')}
                    </strong>

                    <RadioControl
                        selected={state.postType}
                        options={[
                            {label: 'Page', value: 'page'},
                            {label: 'Post', value: 'post'},
                        ]}
                        onChange={value => setState(prevState => {
                            return {
                                ...prevState,
                                postType: value,
                                selected: ''
                            }
                        })}
                    />
                </div>

                <div className="give-embed-modal-row">
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
                </div>

                <div className="give-embed-modal-row">
                    {state.inserted.includes(state.selected) ? (
                        <strong>
                            {__('Form inserted!', 'give')}
                        </strong>
                    ) : (
                        <Button
                            variant="primary"
                            disabled={!state.selected || state.isInserting}
                            isBusy={state.isInserting}
                            onClick={handleInsert}
                        >
                            {state.isInserting ? __('Inserting Form...', 'give') : __('Insert Form', 'give')}
                        </Button>
                    )}

                    <Button
                        variant="secondary"
                        onClick={handleClose}
                        disabled={state.isInserting}
                    >
                        {__('Close', 'give')}
                    </Button>
                </div>
            </div>
        </ModalDialog>
    )
}
