import React from 'react';
import {DesignTab, designTabState} from '@givewp/form-builder/settings/design';
import StyleControls from './style-controls';
import GeneralControls from './general-controls';

type DesignControls = {
    selected: designTabState;
};

/**
 * @unreleased
 */
export default function DesignControls({selected}: DesignControls) {
    if (selected == DesignTab.General) {
        return <GeneralControls />;
    }

    if (selected == DesignTab.Styles) {
        return <StyleControls />;
    }
}
