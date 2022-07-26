import {useSelect} from "@wordpress/data";
import {store as blockEditorStore} from "@wordpress/block-editor/build/store";
import fieldBlocks from "../../../blocks/fields";
import {__} from "@wordpress/i18n";
import BlockTypesList from "@wordpress/block-editor/build/components/block-types-list";
import {SearchControl} from "@wordpress/components";
import {useState} from "react";

const FieldTypesList = () => {

    const [searchValue, setSearchValue] = useState('');

    const store = useSelect(select => {
        return select(blockEditorStore);
    });

    const blocksInUse = store.getBlocks().map(block => {
        return block.innerBlocks.map(innerBlock => innerBlock.name).flat();
    }).flat();

    const blocks = fieldBlocks.map(blockData => {
        return {
            "id": blockData.name,
            "name": blockData.name,
            "category": blockData.category,
            "title": blockData.settings.title,
            "icon": {
                "src": blockData.settings.icon ?? "block-default",
            },
            "isDisabled": !(blockData.settings.supports.multiple ?? true) && blocksInUse.includes(blockData.name),
            // "frecency": ?, // Note: This is not FreQuency, but rather FreCency with combines Frequency with Recency for search ranking.
        };
    });

    const blocksFiltered = blocks.filter(block => block.name.includes(searchValue.toLowerCase().replace(' ', '-')));

    const blocksBySection = blocksFiltered.reduce((sections, block) => {
        sections[block.category].blocks.push(block);
        return sections;
    }, {
        // @todo: Figure out how to handle third-party (or add-on) field categories.
        input: {name: 'input', label: __('Input Fields', 'give'), blocks: []},
        custom: {name: 'custom', label: __('Custom Fields', 'give'), blocks: []},
    });

    return (
        <>
            <div style={{margin: '20px'}}>
                <SearchControl value={searchValue} onChange={setSearchValue} />
            </div>
            {Object.values(blocksBySection).filter(section => section.blocks.length).map(({name, label, blocks}) => {
                return (
                    <>
                        <h3 style={{
                            color: 'var( --give-gray-50 )',
                            margin: '20px',
                            textTransform: 'uppercase',
                            fontSize: '.8em',
                            fontWeight: 500,
                        }}>
                            {label}
                        </h3>
                        <BlockTypesList key={name} items={blocks} />
                    </>
                );
            })}
        </>
    );
};

export default FieldTypesList;
