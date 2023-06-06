import {PanelHeader} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {__experimentalListView as ListView} from '@wordpress/block-editor';

export default function BlockListTree() {
    return (
        <>
            <PanelHeader label={__('List View', 'give')} />
            <ListView showNestedBlocks={true} expandNested={true} />
        </>
    );
};
