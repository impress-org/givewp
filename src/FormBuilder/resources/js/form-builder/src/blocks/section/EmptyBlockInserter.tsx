import {Button, Tooltip} from '@wordpress/components';
import {Inserter} from '@wordpress/block-editor';
import {__, _x} from '@wordpress/i18n';

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
            renderToggle={({onToggle, disabled, isOpen}) => {
                const label = _x('Add block', 'Generic label for block inserter button', 'give');

                return (
                    <Tooltip text={label}>
                        <Button
                            className="block-editor-button-block-appender"
                            onClick={onToggle}
                            aria-haspopup="true" // attribute wants a true value, not a boolean
                            aria-expanded={isOpen}
                            disabled={disabled}
                            label={label}
                        >
                            {__('Drag a block here or click to add a block', 'give')}
                        </Button>
                    </Tooltip>
                );
            }}
            isAppender
        />
    );
}
