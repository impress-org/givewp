import {useSelect} from '@wordpress/data';
import {store as blockEditorStore} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
// @ts-ignore
import {SearchControl} from '@wordpress/components';
import {Fragment, useState} from 'react';
import {BlockInstance} from '@wordpress/blocks';
import {FieldBlock} from '@givewp/form-builder/types';
import BlockTypesList from '@givewp/form-builder/components/forks/BlockTypesList';
import {getBlockRegistrar} from '@givewp/form-builder/common/getWindowData';

// @ts-ignore
const blockRegistrar = getBlockRegistrar();

type SearchBlock = {
    id: string;
    name: string;
    category: 'input' | 'content' | 'custom';
    title: string;
    icon: {
        src: string;
    };
    isDisabled: boolean;
};

const FieldTypesList = () => {
    const [searchValue, setSearchValue] = useState<string>('');

    const store = useSelect<any>((select) => {
        return select(blockEditorStore);
    }, []);

    const blocksInUse = store
        .getBlocks()
        .map((block: BlockInstance) => {
            return block.innerBlocks.map((innerBlock) => innerBlock.name).flat();
        })
        .flat();

    const blocks = blockRegistrar.getAll().map((blockData: FieldBlock) => {
        return {
            id: blockData.name,
            name: blockData.name,
            category: blockData.settings.category,
            title: blockData.settings.title,
            icon: {
                src: blockData.settings.icon ?? 'block-default',
            },
            isDisabled: !(blockData.settings.supports.multiple ?? true) && blocksInUse.includes(blockData.name),
            // "frecency": ?, // Note: This is not FreQuency, but rather FreCency with combines Frequency with Recency for search ranking.
        } as SearchBlock;
    });

    const blocksFiltered = blocks.filter((block) => block.name.includes(searchValue.toLowerCase().replace(' ', '-')));

    const blocksBySection = blocksFiltered.reduce(
        (sections, block) => {
            sections[block.category].blocks.push(block);
            return sections;
        },
        {
            // @todo: Figure out how to handle third-party (or add-on) field categories.
            content: {
                name: 'content',
                label: __('Content & Media', 'give'),
                blocks: [] as SearchBlock[],
            },
            input: {
                name: 'input',
                label: __('Input Fields', 'give'),
                blocks: [] as SearchBlock[],
            },
            custom: {
                name: 'custom',
                label: __('Custom Fields', 'give'),
                blocks: [] as SearchBlock[],
            },
        }
    );

    const sidebarBlocks = Object.values(blocksBySection)
        // @ts-ignore
        .filter((section) => section.blocks.length)
        .map(({name, label, blocks}, index) => {
            return (
                <Fragment key={index}>
                    <h3 className="givewp-next-gen-sidebar__heading">{label}</h3>
                    <BlockTypesList items={blocks} />
                </Fragment>
            );
        });

    return (
        <div className="givewp-next-gen-sidebar__inner">
            <SearchControl value={searchValue} onChange={setSearchValue} />
            <div className="givewp-next-gen-sidebar__inner--blocks">{sidebarBlocks}</div>
        </div>
    );
};

export default FieldTypesList;
