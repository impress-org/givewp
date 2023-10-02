import React, {useState} from 'react';
import {InterfaceSkeleton} from '@wordpress/interface';
import useToggleState from '../hooks/useToggleState';

import HeaderContainer from './HeaderContainer';

import {SecondarySidebar} from '../components';

import {DesignPreview, FormBlocks} from '../components/canvas';
import {useDispatch} from '@wordpress/data';
import {__} from '@wordpress/i18n';
import NoticesContainer from '@givewp/form-builder/containers/NoticesContainer';
import {Sidebar} from '@givewp/form-builder/components';
import DesignSidebar from '../components/sidebar/Design'
import {Button} from "@wordpress/components";
import {listView, plus} from "@wordpress/icons";
import {useEditorState} from "@givewp/form-builder/stores/editor-state";
import EditorMode from "@givewp/form-builder/types/editorMode";
import {useFormState} from "@givewp/form-builder/stores/form-state";

export default function BlockEditorInterfaceSkeletonContainer() {

    const {mode} = useEditorState();

    if(EditorMode.design === mode) {
        return <DesignEditorSkeleton />;
    }

    if(EditorMode.schema === mode) {
        return <SchemaEditorSkeleton />;
    }
}

const DesignEditorSkeleton = () => {
    const {createSuccessNotice} = useDispatch('core/notices');

    const {state: showSidebar, toggle: toggleShowSidebar} = useToggleState(true);
    const {settings: formSettings} = useFormState();

    return (
        <InterfaceSkeleton
            header={
                <HeaderContainer
                    showSidebar={showSidebar}
                    toggleShowSidebar={toggleShowSidebar}
                    onSaveNotice={() => {
                        const notice = 'publish' === formSettings.formStatus
                            ? __('Form updated.', 'give')
                            : __('Form published.', 'give')

                        createSuccessNotice(notice, {
                            type: 'snackbar',
                        });
                    }}
                />
            }
            content={<DesignPreview />}
            sidebar={<DesignSidebar />}
            notices={<NoticesContainer />}
        />
    );
}

const SchemaEditorSkeleton = () => {
    const {createSuccessNotice} = useDispatch('core/notices');

    const {state: showSidebar, toggle: toggleShowSidebar} = useToggleState(true);
    const [selectedSecondarySidebar, setSelectedSecondarySidebar] = useState('');
    const [selectedTab, setSelectedTab] = useState('form');
    const {settings: formSettings} = useFormState();

    const toggleSelectedSecondarySidebar = (name) => setSelectedSecondarySidebar(name !== selectedSecondarySidebar ? name : false)

    const SecondarySidebarButtons = () => {
        return (
            <>
                <div
                    id="AddBlockButtonContainer"
                    style={{
                        padding: 'var(--givewp-spacing-2)',
                        margin: 'calc(var(--givewp-spacing-2) * -1)',
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
        )
    }

    return (
        <InterfaceSkeleton
            header={
                <HeaderContainer
                    SecondarySidebarButtons={SecondarySidebarButtons}
                    showSidebar={showSidebar}
                    toggleShowSidebar={toggleShowSidebar}
                    onSaveNotice={() => {
                        const notice = 'publish' === formSettings.formStatus
                            ? __('Form updated.', 'give')
                            : __('Form published.', 'give')

                        createSuccessNotice(notice, {
                            type: 'snackbar',
                        });
                    }}
                />
            }
            content={<FormBlocks />}
            sidebar={!!showSidebar && <Sidebar selectedTab={selectedTab} setSelectedTab={setSelectedTab} />}
            secondarySidebar={!!selectedSecondarySidebar && <SecondarySidebar selected={selectedSecondarySidebar} />}
            notices={<NoticesContainer />}
        />
    );
}
