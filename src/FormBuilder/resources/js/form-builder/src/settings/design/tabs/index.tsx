import {__} from '@wordpress/i18n';
import {Path, SVG} from '@wordpress/components';
import React from 'react';
import {DesignTab, designTabState} from '@givewp/form-builder/settings/design';

type DesignTabs = {
    close: () => void;
    switchTab: (value: designTabState) => void;
    selected: designTabState;
};

export function DesignTabs({close, switchTab, selected}: DesignTabs) {
    return (
        <div className="components-panel__header interface-complementary-area-header edit-post-sidebar__panel-tabs">
            <ul className={'givewp-block-editor-design-sidebar__tabs'}>
                <li className={'givewp-block-editor-design-sidebar__tab'}>
                    <button
                        type={'button'}
                        aria-label={DesignTab.General}
                        className={` components-button edit-post-sidebar__panel-tab ${
                            selected === DesignTab.General && 'is-active'
                        }`}
                        onClick={() => switchTab(DesignTab.General)}
                    >
                        {__('General', 'give')}
                    </button>
                </li>
                <li className={'givewp-block-editor-design-sidebar__tab'}>
                    <button
                        type={'button'}
                        aria-label={DesignTab.Styles}
                        className={`components-button edit-post-sidebar__panel-tab ${
                            selected === DesignTab.Styles && 'is-active'
                        }`}
                        onClick={() => switchTab(DesignTab.Styles)}
                    >
                        {__('Styles', 'give')}
                    </button>
                </li>
            </ul>
            <button
                type={'button'}
                className={'components-button has-icon'}
                aria-label={'Close Settings'}
                aria-controls={'edit-post:document'}
                onClick={close}
            >
                <SVG
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    width="24"
                    height="24"
                    aria-hidden="true"
                    focusable="false"
                >
                    <Path d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"></Path>
                </SVG>
            </button>
        </div>
    );
}