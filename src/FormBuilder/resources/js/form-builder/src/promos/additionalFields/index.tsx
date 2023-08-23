import React, {useEffect} from 'react';
import ReactDOM from 'react-dom';
import {__} from '@wordpress/i18n';
import LockedFieldBlocks, {LockIcon} from './LockedFieldBlocks';
import './styles.scss';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

/**
 * @unreleased
 */
function AdditionalFields() {
    return (
        <>
            <div className="block-editor-inserter__panel-header">
                <h2 className="block-editor-inserter__panel-title block-editor-inserter__panel-additional-fields-header">
                    {__('Additional Fields', 'give')}
                    <LockIcon />
                </h2>
            </div>

            <div className="block-editor-inserter__panel-content">
                <a
                    className={'block-editor-inserter__panel-content-link'}
                    href="https://docs.givewp.com/vb-upgrade-recurring"
                    target="_blank"
                >
                    {__('Upgrade to unlock additional fields', 'give')}
                </a>
                <LockedFieldBlocks />
            </div>
        </>
    );
}

/**
 * @unreleased
 */
export default function AdditionalFieldsPanel() {
    const {
        formFieldManagerData: {isInstalled},
    } = getFormBuilderWindowData();

    if (isInstalled) {
        return null;
    }

    // Render AdditionalFields panel inside the block list to avoid scrollbar inconsistencies.
    useEffect(() => {
        const blockList = document.querySelector('.block-editor-inserter__block-list');

        if (blockList) {
            const portalElement = document.createElement('div');
            blockList.appendChild(portalElement);

            ReactDOM.render(<AdditionalFields />, portalElement);

            return () => {
                ReactDOM.unmountComponentAtNode(portalElement);
                blockList.removeChild(portalElement);
            };
        }
    }, []);

    return null;
}
