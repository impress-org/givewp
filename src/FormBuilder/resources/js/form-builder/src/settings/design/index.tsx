import React from 'react';
import GeneralControls from '@givewp/form-builder/settings/design/general-controls';
import StyleControls from '@givewp/form-builder/settings/design/style-controls';

export enum DesignSettings {
    General = 'general',
    Styles = 'styles',
}

/**
 * @since 3.4.0
 */
const FormDesignSettings = ({tab}) => {
    if (tab == DesignSettings.General) {
        return <GeneralControls />;
    }

    if (tab == DesignSettings.Styles) {
        return <StyleControls />;
    }
};

export default FormDesignSettings;
