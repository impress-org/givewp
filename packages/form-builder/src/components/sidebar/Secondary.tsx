import {__} from '@wordpress/i18n';
import FieldTypesList from './panels/FieldTypesList';
import BlockListTree from './panels/BlockListTree';

type PropTypes = {
    selected: string;
};

const Sidebar = ({selected}: PropTypes) => {

    const panels = {
        add: FieldTypesList,
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
}

export default Sidebar;
