import {ReactNode} from 'react';
import {SettingsIcon} from '@givewp/form-builder/components/icons';

type DesignSettings = {
    title: string;
    description: string;
    children: ReactNode;
};

/**
 * @since 3.4.0
 */
export default function DesignSettings({title, description, children}: DesignSettings) {
    return (
        <div className={'givewp-block-editor-design-sidebar__settings'}>
            <div className="block-editor-block-inspector">
                <div className="block-editor-block-card">
                    <span className="block-editor-block-icon has-colors">
                        <SettingsIcon />
                    </span>
                    <div className="block-editor-block-card__content">
                        <h4 className="block-editor-block-card__title">{title}</h4>
                        <span className="block-editor-block-card__description">{description}</span>
                    </div>
                </div>
                {children}
            </div>
        </div>
    );
}
