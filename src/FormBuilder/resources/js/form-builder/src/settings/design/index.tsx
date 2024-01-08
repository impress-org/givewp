import {DesignTabs} from '@givewp/form-builder/settings/design/tabs';
import DesignControls from '@givewp/form-builder/settings/design/controls';
import {useState} from 'react';

export enum DesignTab {
    General = 'general',
    Styles = 'styles',
}

export type designTabState = DesignTab.General | DesignTab.Styles;

const FormDesignSettings = ({toggleShowSidebar}) => {
    const [selected, setSelected] = useState<designTabState>(DesignTab.General);
    const switchTab = (value: designTabState) => setSelected(value);
    
    return (
        <div className={'givewp-block-editor-design-sidebar'}>
            <DesignTabs close={toggleShowSidebar} switchTab={switchTab} selected={selected} />
            <DesignControls selected={selected} />
        </div>
    );
};

export default FormDesignSettings;
