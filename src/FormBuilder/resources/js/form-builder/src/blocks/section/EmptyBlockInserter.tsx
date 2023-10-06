import {Button, Tooltip} from '@wordpress/components';
import {Inserter} from '@wordpress/block-editor';
import {forwardRef} from '@wordpress/element';
import {_x, sprintf} from '@wordpress/i18n';

/**
 * The inserter used in sections for dragging and dropping blocks or clicking to add a block.
 */
function EmptyBlockInserter({rootClientId}, ref) {
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
                        _x('Add %s', 'directly add the only allowed block'),
                        blockTitle
                    );
                } else {
                    label = _x('Add block', 'Generic label for block inserter button');
                }
                const isToggleButton = !hasSingleBlockType;

                let inserterButton = (
                    <Button
                        ref={ref}
                        className="block-editor-button-block-appender"
                        onClick={onToggle}
                        aria-haspopup={isToggleButton ? 'true' : undefined}
                        aria-expanded={isToggleButton ? isOpen : undefined}
                        disabled={disabled}
                        label={label}
                    >
                        Drag a block here or click to add a block
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

/**
 * @see https://github.com/WordPress/gutenberg/blob/HEAD/packages/block-editor/src/components/button-block-appender/README.md
 */
export default forwardRef(EmptyBlockInserter);
