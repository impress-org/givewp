import {useState} from 'react';
import {InterfaceSkeleton} from '@wordpress/interface';
import useToggleState from '../hooks/useToggleState';

import HeaderContainer from './HeaderContainer';

import {SecondarySidebar} from '../components';

import {DesignPreview, FormBlocks} from '../components/canvas';
import {useDispatch} from '@wordpress/data';
import {__} from '@wordpress/i18n';
import NoticesContainer from '@givewp/form-builder/containers/NoticesContainer';
import {Sidebar} from '@givewp/form-builder/components';
import {getWindowData} from '@givewp/form-builder/common';

export default function BlockEditorInterfaceSkeletonContainer() {
    const {createSuccessNotice} = useDispatch('core/notices');

    const {state: showSidebar, toggle: toggleShowSidebar} = useToggleState(true);
    const [selectedSecondarySidebar, setSelectedSecondarySidebar] = useState('');
    const [selectedTab, setSelectedTab] = useState('form');
    const {
        formPage: { permalink}
    } = getWindowData();

    return (
        <InterfaceSkeleton
            header={
                <HeaderContainer
                    selectedSecondarySidebar={selectedSecondarySidebar}
                    toggleSelectedSecondarySidebar={(name) =>
                        setSelectedSecondarySidebar(name !== selectedSecondarySidebar ? name : false)
                    }
                    showSidebar={showSidebar}
                    toggleShowSidebar={toggleShowSidebar}
                    onSaveNotice={() => {
                        createSuccessNotice(__('Form updated.', 'give'), {
                            type: 'snackbar',
                            actions: [
                                {
                                    onClick: () => window.open(permalink, '_blank'),
                                    label: __('View', 'give')
                                }
                            ]
                        });
                    }}
                />
            }
            content={'design' === selectedTab ? <DesignPreview /> : <FormBlocks />}
            sidebar={!!showSidebar && <Sidebar selectedTab={selectedTab} setSelectedTab={setSelectedTab} />}
            secondarySidebar={!!selectedSecondarySidebar && <SecondarySidebar selected={selectedSecondarySidebar} />}
            notices={<NoticesContainer />}
        />
    );
}
