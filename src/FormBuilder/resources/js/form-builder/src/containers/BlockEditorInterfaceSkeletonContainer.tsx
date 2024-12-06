import React, {useState} from 'react';
import {InterfaceSkeleton} from '@wordpress/interface';
import useToggleState from '../hooks/useToggleState';

import HeaderContainer from './HeaderContainer';

import {SecondarySidebar} from '../components';

import {DesignPreview, FormBlocks, FormSettings} from '../components/canvas';
import NoticesContainer from '@givewp/form-builder/containers/NoticesContainer';
import {Sidebar} from '@givewp/form-builder/components';
import DesignSidebar from '../components/sidebar/Design';
import {Button} from '@wordpress/components';
import {listView, plus} from '@wordpress/icons';
import {useEditorState} from '@givewp/form-builder/stores/editor-state';
import EditorMode from '@givewp/form-builder/types/editorMode';

export default function BlockEditorInterfaceSkeletonContainer() {
    const {mode} = useEditorState();

    if (EditorMode.schema === mode) {
        return <SchemaEditorSkeleton />;
    }

    if (EditorMode.design === mode) {
        return <DesignEditorSkeleton />;
    }

    if (EditorMode.settings === mode) {
        return <SettingsEditorSkeleton />;
    }
}

const DesignEditorSkeleton = () => {
    const {state: showSidebar, toggle: toggleShowSidebar} = useToggleState(true);

    return (
        <InterfaceSkeleton
            header={<HeaderContainer showSidebar={showSidebar} toggleShowSidebar={toggleShowSidebar} />}
            content={<DesignPreview />}
            sidebar={!!showSidebar && <DesignSidebar toggleShowSidebar={toggleShowSidebar} />}
            notices={<NoticesContainer />}
            className="givewp-form-builder__design-tab"
        />
    );
};

const SchemaEditorSkeleton = () => {
    const {state: showSidebar, toggle: toggleShowSidebar} = useToggleState(true);
    const [selectedSecondarySidebar, setSelectedSecondarySidebar] = useState<string>('add');

    const toggleSelectedSecondarySidebar = (name) =>
        setSelectedSecondarySidebar(name !== selectedSecondarySidebar ? name : false);

    const SecondarySidebarButtons = () => {
        return (
            <>
                <div
                    id="AddBlockButtonContainer"
                    style={{
                        padding: 'var(--givewp-spacing-2)',
                        marginLeft: 'var(--givewp-spacing-4)',
                    }}
                >
                    <Button
                        style={{width: '32px', height: '32px', minWidth: '32px'}}
                        className="rotate-icon"
                        onClick={() => toggleSelectedSecondarySidebar('add')}
                        isPressed={'add' === selectedSecondarySidebar}
                        icon={plus}
                        variant="primary"
                    />
                </div>
                <Button
                    style={{width: '32px', height: '32px'}}
                    onClick={() => toggleSelectedSecondarySidebar('list')}
                    isPressed={'list' === selectedSecondarySidebar}
                    icon={listView}
                />
            </>
        );
    };

    return (
        <InterfaceSkeleton
            header={
                <HeaderContainer
                    SecondarySidebarButtons={SecondarySidebarButtons}
                    showSidebar={showSidebar}
                    toggleShowSidebar={toggleShowSidebar}
                />
            }
            content={<FormBlocks />}
            sidebar={!!showSidebar && <Sidebar />}
            secondarySidebar={!!selectedSecondarySidebar && <SecondarySidebar selected={selectedSecondarySidebar} />}
            notices={<NoticesContainer />}
        />
    );
};

const SettingsEditorSkeleton = () => {
    const {state: showSidebar, toggle: toggleShowSidebar} = useToggleState(true);

    return (
        <InterfaceSkeleton
            header={<HeaderContainer showSidebar={showSidebar} toggleShowSidebar={toggleShowSidebar} />}
            content={<FormSettings />}
            notices={<NoticesContainer />}
            className={'givewp-form-settings__editor'}
        />
    );
};
