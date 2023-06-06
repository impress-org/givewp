/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {Button} from '@wordpress/components';
import {chevronLeft, chevronRight} from '@wordpress/icons';
import {__, isRTL} from '@wordpress/i18n';
import {useDispatch, useSelect} from '@wordpress/data';

/**
 * Internal dependencies
 */
import BlockIcon from './BlockIcon';
import {store as blockEditorStore} from '@wordpress/block-editor';

function BlockCard({title, icon, description, className = ''}) {
    // @ts-ignore
    const isOffCanvasNavigationEditorEnabled = window?.__experimentalEnableOffCanvasNavigationEditor === true;

    const {parentNavBlockClientId} = useSelect((select) => {
        // @ts-ignore
        const {getSelectedBlockClientId, getBlockParentsByBlockName} = select(blockEditorStore);

        const _selectedBlockClientId = getSelectedBlockClientId();

        return {
            parentNavBlockClientId: getBlockParentsByBlockName(_selectedBlockClientId, 'core/navigation', true)[0],
        };
    }, []);

    const {selectBlock} = useDispatch(blockEditorStore);

    return (
        <div className={classnames('block-editor-block-card', className)}>
            {isOffCanvasNavigationEditorEnabled && parentNavBlockClientId && (
                <Button
                    onClick={() => selectBlock(parentNavBlockClientId)}
                    label={__('Go to parent Navigation block')}
                    style={
                        // TODO: This style override is also used in ToolsPanelHeader.
                        // It should be supported out-of-the-box by Button.
                        {minWidth: 24, padding: 0}
                    }
                    icon={isRTL() ? chevronRight : chevronLeft}
                    isSmall
                />
            )}
            {/*@ts-ignore*/}
            <BlockIcon icon={icon} showColors />
            <div className="block-editor-block-card__content">
                <h2 className="block-editor-block-card__title">{title}</h2>
                <span className="block-editor-block-card__description">{description}</span>
            </div>
        </div>
    );
}

export default BlockCard;