export default (blocks) => {
    return blocks.map((section) => {
        return {
            ...section,
            innerBlocks: section.innerBlocks.map((block) => {
                return block.name !== 'givewp/text' ? block : {
                    ...block,
                    attributes: {
                        label: block.attributes.label + '!',
                    }
                }
            })
        };
    });
}
