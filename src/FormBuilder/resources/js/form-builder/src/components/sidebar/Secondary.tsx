import {__} from '@wordpress/i18n';
import BlockListTree from './panels/BlockListTree';
import {__experimentalLibrary as Library} from '@wordpress/block-editor';
import AdditionalFields from '@givewp/form-builder/promos/additionalFields';

const BlockListInserter = () => {
    return (
        <>
            <Library showInserterHelpPanel={false} />
            <AdditionalFields />
        </>
    );
};

type PropTypes = {
    selected: string;
};

const Sidebar = ({selected}: PropTypes) => {
    const panels = {
        add: BlockListInserter,
        list: BlockListTree,
    };

    const PanelContent = panels[selected];

    /* eslint-disable react/jsx-pascal-case */
    return (
        <div
            id="sidebar-secondary"
            className="givewp-next-gen-sidebar
             givewp-next-gen-sidebar-secondary"
            role="region"
            aria-label={__('Standalone Block Editor advanced settings.')}
            tabIndex={-1}
        >
            <PanelContent />
        </div>
    );
};

export default Sidebar;
