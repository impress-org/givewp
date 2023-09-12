import {__} from '@wordpress/i18n';
import BlockListTree from './panels/BlockListTree';
import {__experimentalLibrary as Library} from '@wordpress/block-editor';
import AdditionalFieldsPanel from '@givewp/form-builder/promos/additionalFields';
import {useSelect} from '@wordpress/data';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

const BlockListInserter = () => {
    // @ts-ignore
    const selectedBlock = useSelect((select) => select('core/block-editor').getSelectedBlock(), []);
    const isPrimaryBlockList = !!selectedBlock && selectedBlock.name !== 'givewp/section';
    const {
        formFieldManagerData: {isInstalled},
    } = getFormBuilderWindowData();

    const showAdditionalFieldsPanel = !isInstalled && isPrimaryBlockList;

    return (
        <div>
            <Library showInserterHelpPanel={false} />
            {showAdditionalFieldsPanel && <AdditionalFieldsPanel />}
        </div>
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
