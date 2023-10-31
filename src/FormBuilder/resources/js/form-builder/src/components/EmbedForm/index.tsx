import {useState} from 'react';
import {useSelect} from '@wordpress/data';
import {store} from '@wordpress/core-data';
import {__} from '@wordpress/i18n';
import {Button, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import getWindowData from '@givewp/form-builder/common/getWindowData';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';

import './styles.scss';

export default function EmbedFormModal({handleClose}) {

    const {formId} = getWindowData();
    const [postType, setPostType] = useState<string>('page');
    const [selected, setSelected] = useState<string>('');
    const [isCopied, setIsCopied] = useState<boolean>(false);
    const shortcode = `[give_form id=${formId}]`;

    // Get pages
    const pages = useSelect((select) => {
        const pages = [];
        const {getEntityRecords} = select(store);

        const query = {
            status: 'publish',
            per_page: -1
        }

        const data = getEntityRecords('postType', postType, query);

        const selectLabel = 'page' === postType
            ? __('Select a page', 'give')
            : __('Select a post', 'give')

        pages.push({value: '', label: selectLabel, disabled: true});

        data?.forEach(page => {
            pages.push({value: page.id, label: page.title.rendered})
        });

        return pages;

    }, [postType]);


    const handleCopy = () => {
        navigator.clipboard.writeText(shortcode);

        setIsCopied(true);

        setTimeout(() => {
            setIsCopied(false);
        }, 2000);
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
                                {isCopied ? __('Copied!', 'give') : __('Copy Shortcode', 'give')}
                            </Button>
                        </div>
                    </div>
                </div>

                <div className="give-embed-modal-row give-embed-modal-row-radio">

                    <strong>
                        {__('Add to existing content', 'give')}
                    </strong>

                    <RadioControl
                        selected={postType}
                        options={[
                            {label: 'Page', value: 'page'},
                            {label: 'Post', value: 'post'},
                        ]}
                        onChange={value => {
                            setPostType(value);
                            setSelected('');
                        }}
                    />
                </div>

                <div className="give-embed-modal-row">
                    <SelectControl
                        value={selected}
                        options={pages}
                        onChange={value => setSelected(value)}
                    />
                </div>

                <div className="give-embed-modal-row">
                    <Button
                        variant="secondary"
                        disabled={!selected}
                    >
                        {__('Insert Form', 'give')}
                    </Button>
                </div>

            </div>
        </ModalDialog>
    )
}
