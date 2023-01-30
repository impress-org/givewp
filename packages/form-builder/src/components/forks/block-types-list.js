/**
 * @note This is a fork of the WordPress component.
 *
 * @see @wordpress/block-editor/src/components/block-types-list
 *
 * @since 0.1.0
 *
 * Substantive changes:
 *  - Updates the isDraggable prop in InserterListItem to account for isDisabled.

 /**
 * Internal @wordpress dependencies
 */
import InserterListItem from '@wordpress/block-editor/build/components/inserter-list-item';
import {InserterListboxGroup, InserterListboxRow} from '@wordpress/block-editor/build/components/inserter-listbox';

import {getBlockMenuDefaultClassName} from '@wordpress/blocks';

function chunk(array, size) {
    const chunks = [];
    for (let i = 0, j = array.length; i < j; i += size) {
        chunks.push(array.slice(i, i + size));
    }
    return chunks;
}

function BlockTypesList({
                            items = [],
                            onSelect,
                            onHover = () => {
                            },
                            children,
                            label,
                            isDraggable = true,
                        }) {
    return (
        <InserterListboxGroup
            className="block-editor-block-types-list"
            aria-label={label}
        >
            {chunk(items, 3).map((row, i) => (
                <InserterListboxRow key={i}>
                    {row.map((item, j) => (
                        <InserterListItem
                            key={item.id}
                            item={item}
                            className={getBlockMenuDefaultClassName(
                                item.id
                            )}
                            onSelect={onSelect}
                            onHover={onHover}
                            isDraggable={isDraggable && !item.isDisabled}
                            isFirst={i === 0 && j === 0}
                        />
                    ))}
                </InserterListboxRow>
            ))}
            {children}
        </InserterListboxGroup>
    );
}

export default BlockTypesList;
