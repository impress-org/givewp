import {FormSettings} from '@givewp/form-builder/types';

/**
 * @since 3.4.0
 */
function updateDesignSettingsClassName(root: HTMLElement, block, element) {
    root.classList.forEach((className) => {
        if (className.startsWith(block + '__')) {
            root.classList.remove(className);
        }
    });
    root.classList.add(block + '__' + element);
}

/**
 * @note currently not in use
 * @since 3.4.0
 */
export default function setDesignSettings(root: HTMLElement, settings: FormSettings) {
    if (settings['designSettingsSectionStyle']) {
        updateDesignSettingsClassName(
            root,
            'givewp-design-settings--section-style',
            settings['designSettingsSectionStyle']
        );
    }

    if (settings['designSettingsImageUrl']) {
        root.style.setProperty(
            '--givewp-design-settings-background-image',
            'url(' + settings['designSettingsImageUrl'] + ')'
        );

        const style = settings['designSettingsImageStyle'] ? settings['designSettingsImageStyle'] : 'background';

        updateDesignSettingsClassName(root, 'givewp-design-settings--image-style', style);
    }

    if (settings['designSettingsLogoUrl']) {
        root.style.setProperty('--givewp-design-settings-logo', 'url(' + settings['designSettingsLogoUrl'] + ')');
        root.classList.add('givewp-design-settings--logo');

        const position = settings['designSettingsLogoPosition'] ? settings['designSettingsLogoPosition'] : 'left';
        updateDesignSettingsClassName(root, 'givewp-design-settings--logo-position', position);
    }

    if (settings['designSettingsTextFieldStyle']) {
        updateDesignSettingsClassName(
            root,
            'givewp-design-settings--textField-style',
            settings['designSettingsTextFieldStyle']
        );
    }

    if (!settings['designSettingsImageUrl']) {
        // reset/remove classnames on delete
        root.style.setProperty('--givewp-design-settings-background-image', '');
        updateDesignSettingsClassName(root, 'givewp-design-settings--image-style', '');

        // reconstruct branding container & logo container
        root.classList.add('givewp-design-settings--logo');
        root.style.setProperty('--givewp-design-settings-logo', 'url(' + settings['designSettingsLogoUrl'] + ')');
        updateDesignSettingsClassName(
            root,
            'givewp-design-settings--logo-position',
            settings['designSettingsLogoPosition']
        );
    }
}
