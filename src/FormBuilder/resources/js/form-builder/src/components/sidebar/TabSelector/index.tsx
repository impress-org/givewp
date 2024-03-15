import {Button, Path, SVG} from '@wordpress/components';
import React from 'react';

type TabSelector = {
    close: () => void;
    selectTab: (value: string) => void;
    selected: string;
    tabs: {name: string; label: string}[];
};

/**
 * @since 3.4.0
 */
export function TabSelector({close, selectTab, selected, tabs}: TabSelector) {
    return (
        <div className="components-panel__header interface-complementary-area-header edit-post-sidebar__panel-tabs">
            <ul className={'givewp-block-editor-sidebar__tabs'}>
                {tabs.map(({name, label}) => (
                    <li className={'givewp-block-editor-sidebar__tab'}>
                        <Button
                            type={'button'}
                            aria-label={label}
                            aria-selected={selected === name}
                            className={` components-button edit-post-sidebar__panel-tab ${
                                selected === name && 'is-active'
                            }`}
                            onClick={() => selectTab(name)}
                        >
                            {label}
                        </Button>
                    </li>
                ))}
            </ul>
            <Button
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
            </Button>
        </div>
    );
}
