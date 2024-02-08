import {__} from '@wordpress/i18n';
import FormDesignSettings, {DesignSettings} from '@givewp/form-builder/settings/design';
import {useState} from 'react';
import {TabSelector} from '@givewp/form-builder/components/sidebar/TabSelector';

type designTabState = DesignSettings.General | DesignSettings.Styles;

/**
 * @since 3.4.0 add FormDesignSetting tabs.
 */
const Sidebar = ({toggleShowSidebar}) => {
    const [selected, setSelected] = useState<designTabState>(DesignSettings.General);
    const selectTab = (value: designTabState) => setSelected(value);

    const designSettingTabs = [
        {
            name: DesignSettings.General,
            label: __('General', 'give'),
        },
        {
            name: DesignSettings.Styles,
            label: __('Styles', 'give'),
        },
    ];

    return (
        <div
            id="sidebar-primary"
            className="givewp-next-gen-sidebar givewp-next-gen-sidebar-primary"
            role="region"
            aria-label={__('Form design settings')}
            tabIndex={-1}
        >
            <TabSelector close={toggleShowSidebar} selectTab={selectTab} selected={selected} tabs={designSettingTabs} />
            <FormDesignSettings tab={selected} />
        </div>
    );
};

export default Sidebar;
