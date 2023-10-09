import {Button, Tooltip} from '@wordpress/components';
import {Inserter} from '@wordpress/block-editor';
import {_x, sprintf} from '@wordpress/i18n';

/**
 * The inserter used in sections for dragging and dropping blocks or clicking to add a block.
 */
export default function EmptyBlockInserter({rootClientId}) {
    return (
        <Inserter
            position="bottom center"
            rootClientId={rootClientId}
            __experimentalIsQuick
            className="give-section__empty-block-inserter"
            renderToggle={({onToggle, disabled, isOpen, blockTitle, hasSingleBlockType}) => {
                let label;
                if (hasSingleBlockType) {
                    label = sprintf(
                        // translators: %s: the name of the block when there is only one
                        _x('Add %s', 'directly add the only allowed block', 'give'),
                        blockTitle
                    );
                } else {
                    label = _x('Add block', 'Generic label for block inserter button', 'give');
                }
                const isToggleButton = !hasSingleBlockType;

                let inserterButton = (
                    <Button
                        className="block-editor-button-block-appender"
                        onClick={onToggle}
                        aria-haspopup={isToggleButton ? 'true' : undefined}
                        aria-expanded={isToggleButton ? isOpen : undefined}
                        disabled={disabled}
                        label={label}
                    >
                        {__('Drag a block here or click to add a block', 'give')}
                    </Button>
                );

                if (isToggleButton || hasSingleBlockType) {
                    inserterButton = <Tooltip text={label}>{inserterButton}</Tooltip>;
                }
                return inserterButton;
            }}
            isAppender
        />
    );
}
