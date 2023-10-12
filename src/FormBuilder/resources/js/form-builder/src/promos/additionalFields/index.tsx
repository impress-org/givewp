import React from 'react';
import {__} from '@wordpress/i18n';
import LockedFieldBlocks, {LockIcon} from './LockedFieldBlocks';
import './styles.scss';

/**
 * @since 3.0.0
 */
export default function AdditionalFieldsPanel() {
    return (
        <>
            <div className={'block-editor-inserter__panel-header'}>
                <h2
                    className={
                        'block-editor-inserter__panel-title block-editor-inserter__panel-additional-fields-header'
                    }
                >
                    {__('Additional Fields', 'give')}
                    <LockIcon />
                </h2>
            </div>

            <div className="block-editor-inserter__panel-content">
                <a
                    className={'block-editor-inserter__panel-content-link'}
                    href={'https://docs.givewp.com/vb-add-fields'}
                    target={'_blank'}
                    rel="noopener noreferrer"
                >
                    {__('Upgrade to unlock additional fields', 'give')}
                </a>
                <LockedFieldBlocks />
            </div>
        </>
    );
}
