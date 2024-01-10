import {__} from '@wordpress/i18n';
import FormDesignSettings from '@givewp/form-builder/settings/design';

const Sidebar = ({toggleShowSidebar}) => {
    return (
        <div
            id="sidebar-primary"
            className="givewp-next-gen-sidebar givewp-next-gen-sidebar-primary"
            role="region"
            aria-label={__('Form design settings')}
            tabIndex={-1}
        >
            <FormDesignSettings toggleShowSidebar={toggleShowSidebar} />
        </div>
    );
};

export default Sidebar;
